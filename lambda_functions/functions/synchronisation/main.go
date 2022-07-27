package main

import (
	"context"
	"crypto/tls"
	"strconv"
	"log"
	"net/http"
	"os"

	runtime "github.com/aws/aws-lambda-go/lambda"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/lambda"
	"github.com/aws/aws-sdk-go/service/secretsmanager"
)

var client = lambda.New(session.New())

type Input struct {
    Command string `json:"command"`
}

type LamdbaResponse struct {
    msg string
	status int
}

func getSecret() string {
	secretPrefix := os.Getenv("SECRETS_PREFIX");
	secretName := secretPrefix + "/synchronise-jwt"
	region := "eu-west-1"
	sess := session.Must(session.NewSession())

	svc := secretsmanager.New(sess, aws.NewConfig().WithRegion(region))

	localStackEndpoint := os.Getenv("LOCALSTACK_ENDPOINT");
	if  localStackEndpoint != "" {
		svc.Endpoint = localStackEndpoint
	}
	result, err := svc.GetSecretValue(&secretsmanager.GetSecretValueInput{SecretId: &secretName})
	if err != nil {
		log.Fatal(err.Error())
	}
	return *result.SecretString
}

func handleRequest(ctx context.Context, event Input) (string, error) {
	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")

	log.Println("Starting kickoff of " + event.Command)

	jwt := getSecret()

	var suffix string;
	if event.Command == "documents" {
		suffix = "/synchronise/documents"
	} else {
		suffix = "/synchronise/checklists"
	}

	urlFinal := url + suffix
	//Internal call trusted (until we remove TLS at load balancer anyway)
	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}

	client := http.Client{}
	req , err := http.NewRequest("POST", urlFinal, nil)
	req.Header.Set("JWT", jwt)

	res, err := client.Do(req)
	if err != nil {
		log.Printf("failed to call remote service: (%v)\n", err)
	}

	defer res.Body.Close()

	message := "Completed " + event.Command + " kickoff!"
	if res.StatusCode != 200 {
		message = "Error kicking off " + event.Command
	}

	lambdaResponse := LamdbaResponse{msg: message, status: res.StatusCode}

	lambdaResponseString := "{message: " + lambdaResponse.msg + " status: " + strconv.Itoa(lambdaResponse.status) + "}"

	return lambdaResponseString, nil
}

func main() {
	runtime.Start(handleRequest)
}
