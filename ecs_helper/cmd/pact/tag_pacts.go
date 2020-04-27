package main

import (
	"os"
	"fmt"
	"net/http"
	"encoding/base64"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/secretsmanager"
)

func basicAuth(username, password string) string {
	auth := username + ":" + password
	return base64.StdEncoding.EncodeToString([]byte(auth))
}

func getPactPassword(roletoassume string, pactsecretkey string) string {
	mysession := session.Must(session.NewSession())
	creds := stscreds.NewCredentials(mysession, roletoassume)
	cfg := aws.Config{Credentials: creds,Region: aws.String("eu-west-1")}
	sess := session.Must(session.NewSession(&cfg))
	sm := secretsmanager.New(sess)
	secretkey := pactsecretkey
	pact_password, err := sm.GetSecretValue(&secretsmanager.GetSecretValueInput{SecretId: &secretkey})
	if err != nil {
		panic(err.Error())
	}
	return *pact_password.SecretString
}
// func redirectPolicyFunc(req *http.Request, via []*http.Request, pact_password string) error {
//  req.Header.Add("Authorization","Basic " + basicAuth("admin",pact_password))
//  return nil
// }

func main() {

	base_url := os.Getenv("PACT_BROKER_BASE_URL")
	consumer_version := os.Getenv("PACT_CONSUMER_VERSION")
	api_version := os.Getenv("PACT_API_VERSION")

	consumer := "Complete%20the%20deputy%20report"
	account := "997462338508"
	roletoassume := fmt.Sprintf("arn:aws:iam::%s:role/get-pact-secret-production", account)
	pactsecretkey := "pactbroker_admin"

	pact_password := getPactPassword(roletoassume, pactsecretkey)

	url := "https://"  + base_url + "/pacticipants/" + consumer + "/versions/" + consumer_version
	req, err := http.NewRequest("GET", url, nil)
	req.Header.Add("Authorization","Basic " + basicAuth("admin",pact_password))

	response, err := http.DefaultClient.Do(req)
	if response.Status == "200 OK" {
		url = "https://"  + base_url + "/pacticipants/" + consumer + "/versions/" + consumer_version + "/tags/" + api_version + "_production"
		req, err = http.NewRequest("PUT", url, nil)
		req.Header.Add("Authorization","Basic " + basicAuth("admin",pact_password))
		req.Header.Set("Content-Type", "application/json")
		response, err = http.DefaultClient.Do(req)
		if err != nil {
			panic(err.Error())
		}
		fmt.Printf("Successfully updated pact broker. Tagged %s with %s_production", consumer_version, api_version)

	} else {
		fmt.Printf("Version doesn't exist with commit %s, getting status code %s", consumer_version, response.Status)
	}
}
