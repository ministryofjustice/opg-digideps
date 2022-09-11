package main

import (
	"context"
	"errors"
	"io/ioutil"
	"net/http"
	"os"
	"strings"
	"testing"

	"github.com/aws/aws-sdk-go/service/secretsmanager"
	"github.com/aws/aws-sdk-go/service/secretsmanager/secretsmanageriface"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
)

// Define a mock struct to be used in your unit tests of myFunc.
type mockSecretsManagerClient struct {
	secretsmanageriface.SecretsManagerAPI
	mock.Mock
}

func (m *mockSecretsManagerClient) GetSecretValue(input *secretsmanager.GetSecretValueInput) (*secretsmanager.GetSecretValueOutput, error) {
    secretValue := "mysecret"
	errorMessage := "secret not found"
	if *input.SecretId == "local/synchronisation-jwt-token" {
		return &secretsmanager.GetSecretValueOutput{SecretString: &secretValue}, nil
	} else {
		return nil, &secretsmanager.InternalServiceError{Message_: &errorMessage}
	}
}

type DigidepsClientMock struct {
	http.Client
	mock.Mock
}

func (d *DigidepsClientMock) Do(req *http.Request) (resp *http.Response, err error) {
	outputs := d.Called(req)
	return outputs.Get(0).(*http.Response), outputs.Error(1)
}

type HandleEventSuite struct {
	suite.Suite
	l Lambda
	secretsManagerClientMock *mockSecretsManagerClient
	DDClientMock *DigidepsClientMock
}

func (suite *HandleEventSuite) SetupTest() {
	suite.secretsManagerClientMock = new(mockSecretsManagerClient)
	suite.DDClientMock = new(DigidepsClientMock)
	suite.l = Lambda{
		secretsManagerClient: suite.secretsManagerClientMock,
		digidepsClient: suite.DDClientMock,
	}
}

func ErrorContains(out error, want string) bool {
    if out == nil {
        return want == ""
    }
    if want == "" {
        return false
    }
    return strings.Contains(out.Error(), want)
}

func CreateError(err string) error {
	if err == "" {
		return nil
	} else {
		return errors.New(err)
	}
}

func TestGetSecret(t *testing.T) {
	mockSvc := &mockSecretsManagerClient{}
	cases := []struct {
		prefix, expectedValue, expectedError string
	}{
		{prefix: "local", expectedValue: "mysecret", expectedError: ""},
		{prefix: "", expectedValue: "", expectedError: "SECRETS_PREFIX environment variable not set"},
		{prefix: "wrong", expectedValue: "", expectedError: "InternalServiceError: secret not found"},
	}

	for _, tt := range cases {
		//setup variables

		_ = os.Unsetenv("SECRETS_PREFIX")
		if tt.prefix != "" {
			_ = os.Setenv("SECRETS_PREFIX", tt.prefix)
		}


		rescueStdout := os.Stdout
		r, w, _ := os.Pipe()
		os.Stdout = w

		secret, err := GetSecret(mockSvc)

		w.Close()
		out, _ := ioutil.ReadAll(r)
		os.Stdout = rescueStdout

		t.Logf("Output: %s", out)

		if ErrorContains(err, tt.expectedError) {
			t.Logf("expected '%s' and got '%s'", tt.expectedError, err)
		} else {
			t.Errorf("expected '%s' and got '%s'", tt.expectedError, err)
		}

		if secret == tt.expectedValue {
			t.Logf("expected '%s' and got '%s'", tt.expectedValue, secret)
		} else {
			t.Errorf("expected '%s' and got '%s'", tt.expectedValue, secret)
		}
	}
}

func (suite *HandleEventSuite) TestHandleEvent() {
	cases := []struct {
		endpoint, command, secretPrefix, resError, expectedResponse, expectedError string
		resStatus int
	}{
		{
			endpoint: "https://localhost", command: "documents", secretPrefix: "local",
			resError: "service unavailable", resStatus: 500, expectedResponse: "", expectedError: "failed to call remote service: (service unavailable)\n",
		},
		{
			endpoint: "https://localhost", command: "documents", secretPrefix: "local",
			resError: "", resStatus: 401, expectedResponse: "", expectedError: "failed to send with response status: 401",
		},
		{
			endpoint: "https://localhost", command: "documents", secretPrefix: "local",
			resError: "", resStatus: 200, expectedResponse: "successfully called sync process", expectedError: "",
		},
		{
			endpoint: "https://localhost", command: "checklists", secretPrefix: "local",
			resError: "", resStatus: 200, expectedResponse: "successfully called sync process", expectedError: "",
		},
		{
			endpoint: "", command: "documents", secretPrefix: "local",
			resError: "", resStatus: 200, expectedResponse: "", expectedError: "DIGIDEPS_SYNC_ENDPOINT environment variable not set",
		},
		{
			endpoint: "https://localhost", command: "wrong", secretPrefix: "local",
			resError: "", resStatus: 200, expectedResponse: "", expectedError: "input not set to valid sync type",
		},
				{
			endpoint: "https://localhost", command: "documents", secretPrefix: "wrong",
			resError: "", resStatus: 200, expectedResponse: "", expectedError: "InternalServiceError: secret not found",
		},
	}

	for _, tt := range cases {
		_ = os.Setenv("SECRETS_PREFIX", tt.secretPrefix)
		_ = os.Setenv("DIGIDEPS_SYNC_ENDPOINT", tt.endpoint)
		secretName := "local/synchronisation-jwt-token"
		secretValue := "mysecret"
		input := &secretsmanager.GetSecretValueInput{SecretId: &secretName}
		output := &secretsmanager.GetSecretValueOutput{SecretString: &secretValue}

		suite.secretsManagerClientMock.On("GetSecretValue", &input).Return(&output, nil).Once()

		req , _ := http.NewRequest("POST", "https://localhost/synchronise/" + tt.command, nil)
		req.Header.Set("JWT", secretValue)

		suite.DDClientMock.On("Do", req).Return(&http.Response{StatusCode: tt.resStatus}, CreateError(tt.resError)).Once()

		event := Input{Command: tt.command}
		var ctx context.Context

		rescueStdout := os.Stdout
		r, w, _ := os.Pipe()
		os.Stdout = w

		response, err := suite.l.HandleEvent(ctx, event)

		w.Close()
		out, _ := ioutil.ReadAll(r)
		os.Stdout = rescueStdout
		suite.T().Logf("Output: %s", out)

		suite.Assert().Equal(tt.expectedResponse, response)
		if err != nil {
			suite.Assert().EqualError(err, tt.expectedError)
		} else {
			suite.Assert().ErrorIs(err, CreateError(tt.expectedError))
		}
	}
}

func TestHandleEventSuite(t *testing.T) {
	suite.Run(t, new(HandleEventSuite))
}
