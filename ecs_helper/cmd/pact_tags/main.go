package main

import (
	"encoding/base64"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/secretsmanager"
	"net/http"
	"os"
)

func basicAuth(username, password string) string {
	auth := username + ":" + password
	return base64.StdEncoding.EncodeToString([]byte(auth))
}

func getPactPassword(roleToAssume string, pactSecretKey string) string {
	mysession := session.Must(session.NewSession())
	creds := stscreds.NewCredentials(mysession, roleToAssume)
	cfg := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	sess := session.Must(session.NewSession(&cfg))
	sm := secretsmanager.New(sess)
	pactPassword, err := sm.GetSecretValue(&secretsmanager.GetSecretValueInput{SecretId: &pactSecretKey})
	if err != nil {
		panic(err.Error())
	}
	return *pactPassword.SecretString
}

func main() {

	baseUrl := os.Getenv("PACT_BROKER_BASE_URL")
	consumerVersion := os.Getenv("PACT_CONSUMER_VERSION")
	apiVersion := os.Getenv("PACT_API_VERSION")
	account := os.Getenv("PACT_BROKER_ACCOUNT")

	if len(baseUrl) == 0 || len(consumerVersion) == 0 || len(apiVersion) == 0 || len(account) == 0 {
		panic("One or more environment variables not set! Exiting")
	}

	consumer := "Complete%20the%20deputy%20report"
	roleToAssume := fmt.Sprintf("arn:aws:iam::%s:role/get-pact-secret-production", account)
	pactSecretKey := "pactbroker_admin"

	pactPassword := getPactPassword(roleToAssume, pactSecretKey)

	url := "https://" + baseUrl + "/pacticipants/" + consumer + "/versions/" + consumerVersion
	req, err := http.NewRequest("GET", url, nil)
	req.Header.Add("Authorization", "Basic "+basicAuth("admin", pactPassword))

	response, err := http.DefaultClient.Do(req)
	if err != nil {
		panic(err.Error())
	}
	if response.Status == "200 OK" {
		url = "https://" + baseUrl + "/pacticipants/" + consumer + "/versions/" + consumerVersion + "/tags/" + apiVersion + "_production"
		req, err = http.NewRequest("PUT", url, nil)
		req.Header.Add("Authorization", "Basic "+basicAuth("admin", pactPassword))
		req.Header.Set("Content-Type", "application/json")
		response, err = http.DefaultClient.Do(req)
		if err != nil {
			panic(err.Error())
		}
		fmt.Printf("Successfully updated pact broker. Tagged %s with %s_production\n", consumerVersion, apiVersion)

	} else {
		fmt.Printf("Version doesn't exist with commit %s, getting status code %s\n", consumerVersion, response.Status)
	}
}
