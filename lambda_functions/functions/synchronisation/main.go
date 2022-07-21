package main

import (
	"bytes"
	"context"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strings"

	"github.com/aws/aws-lambda-go/events"
	runtime "github.com/aws/aws-lambda-go/lambda"

	// "github.com/aws/aws-lambda-go/lambdacontext"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/lambda"
)

var client = lambda.New(session.New())

func callLambda() (string, error) {
	input := &lambda.GetAccountSettingsInput{}
	req, resp := client.GetAccountSettingsRequest(input)
	err := req.Send()
	output, _ := json.Marshal(resp.AccountUsage)
	return string(output), err
}

func handleRequest(ctx context.Context, event events.SQSEvent) (string, error) {


	//Grab the secret

	//Call the endpoint


	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")

	//Data to load
	// json, err := ioutil.ReadFile("report_payload.json") // just pass the file name
	// if err != nil {
	// 		fmt.Print(err)
	// }
    // body := strings.NewReader(string(json))

	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	body := strings.NewReader("{}")
	res, err := http.Post(url, "application/json", body)
    // req.Header.Set("Content-Type", "application/json")

	// res, err := http.DefaultClient.Do(req)
	if err != nil {
		log.Printf("failed to call remote service: (%v)\n", err)
	}

	defer res.Body.Close()

	buf := new(bytes.Buffer)
	buf.ReadFrom(res.Body)
	newStr := buf.String()

    fmt.Println(res.StatusCode)
    fmt.Println(res.Status)
	fmt.Println(newStr)




	// event
	// eventJson, _ := json.MarshalIndent(event, "", "  ")
	// log.Printf("EVENT: %s", eventJson)
	// // environment variables
	// log.Printf("REGION: %s", os.Getenv("AWS_REGION"))
	// log.Println("ALL ENV VARS:")
	// for _, element := range os.Environ() {
	// 	log.Println(element)
	// }
	// // request context
	// lc, _ := lambdacontext.FromContext(ctx)
	// log.Printf("REQUEST ID: %s", lc.AwsRequestID)
	// // global variable
	// log.Printf("FUNCTION NAME: %s", lambdacontext.FunctionName)
	// // context method
	// deadline, _ := ctx.Deadline()
	// log.Printf("DEADLINE: %s", deadline)
	// // AWS SDK call
	// usage, err := callLambda()
	// if err != nil {
	// 	return "ERROR", err
	// }
	return "Completed Document Kickoff", nil
}

func main() {
	runtime.Start(handleRequest)
}
