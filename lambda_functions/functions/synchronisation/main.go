package main

import (
	"context"
	"crypto/tls"
	"errors"
	"fmt"
	"log"
	"net/http"
	"os"
	runtime "github.com/aws/aws-lambda-go/lambda"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/lambda"
	"github.com/aws/aws-sdk-go/service/secretsmanager"
	"github.com/aws/aws-sdk-go/service/secretsmanager/secretsmanageriface"
)

var client = lambda.New(session.New())

type Input struct {
    Command string `json:"command"`
}

// DigidepsClient is a interface for a http client
// Interface is used to allow for mocking
type DigidepsClient interface {
	Do(req *http.Request) (*http.Response, error)
}

type Lambda struct {
	secretsManagerClient secretsmanageriface.SecretsManagerAPI
	digidepsClient DigidepsClient
}

func GetSecret(svc secretsmanageriface.SecretsManagerAPI) (string, error) {
	secretPrefix := os.Getenv("SECRETS_PREFIX");

	if secretPrefix == "" {
		msg := "SECRETS_PREFIX environment variable not set"
		log.Print(msg)
		return "", errors.New(msg)
	}

	secretName := secretPrefix + "/synchronisation-jwt-token"

	result, err := svc.GetSecretValue(&secretsmanager.GetSecretValueInput{SecretId: &secretName})
	if err != nil {
		msg := fmt.Sprintf("%s", err)
		log.Print(msg)
		return "", errors.New(msg)
	}

	return *result.SecretString, err
}

func IsValidSyncType(syncType string) bool {
    switch syncType {
    case
        "documents",
        "checklists":
        return true
    }
    return false
}

func (l *Lambda) HandleEvent(ctx context.Context, event Input) (string, error) {
	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")
	if url == "" {
		msg := "DIGIDEPS_SYNC_ENDPOINT environment variable not set"
		log.Print(msg)
		return "", errors.New(msg)
	}

	if !IsValidSyncType(event.Command) {
		msg := "input not set to valid sync type"
		log.Print(msg)
		return "", errors.New(msg)
	}

	log.Println("Starting kickoff of " + event.Command)

	jwt, err := GetSecret(l.secretsManagerClient)
	if err != nil {
		return "", err
	}

	var suffix string;

	if event.Command == "documents" {
		suffix = "/synchronise/documents"
	} else {
		suffix = "/synchronise/checklists"
	}

	urlFinal := url + suffix
	//Internal call trusted (until we remove TLS at load balancer anyway)
	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}

	client := l.digidepsClient

	req , err := http.NewRequest("POST", urlFinal, nil)
	req.Header.Set("JWT", jwt)

	res, err := client.Do(req)
	if err != nil {
		msg := fmt.Sprintf("failed to call remote service: (%v)\n", err)
		log.Print(msg)
		return "", errors.New(msg)
	}

	if res.StatusCode != 200 {
		msg := fmt.Sprintf("failed to send with response status: %v", res.StatusCode)
		log.Print(msg)
		return "", errors.New(msg)
	}

	msg := "successfully called sync process"
	log.Println(msg)

	return msg, nil
}

func GetSecretService() *secretsmanager.SecretsManager {
	region := "eu-west-1"
	sess := session.Must(session.NewSession())
	svc := secretsmanager.New(sess, aws.NewConfig().WithRegion(region))
	localStackEndpoint := os.Getenv("LOCALSTACK_ENDPOINT");
	if  localStackEndpoint != "" {
		svc.Endpoint = localStackEndpoint
	}
	return svc
}

func main() {
	l := Lambda{
		secretsManagerClient: GetSecretService(),
		digidepsClient: &http.Client{},
	}
	runtime.Start(l.HandleEvent)
}
