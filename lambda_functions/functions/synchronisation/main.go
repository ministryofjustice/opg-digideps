package main

import (
	"bytes"
	"context"
	"crypto/tls"
	"log"
	"net/http"
	"os"
	"strings"

	"github.com/aws/aws-lambda-go/events"
	runtime "github.com/aws/aws-lambda-go/lambda"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/lambda"
)

var client = lambda.New(session.New())

func handleRequest(ctx context.Context, event events.SQSEvent) (string, error) {
	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")

	//Internal call trusted (until we remove TLS at load balancer anyway)
	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	body := strings.NewReader("{}")
	res, err := http.Post(url, "application/json", body)
 	log.Println(event)
	log.Println(ctx)
	if err != nil {
		log.Printf("failed to call remote service: (%v)\n", err)
	}

	defer res.Body.Close()

	buffer := new(bytes.Buffer)
	buffer.ReadFrom(res.Body)
	responseBody := buffer.String()


    log.Println(res.StatusCode)
    log.Println(res.Status)
	log.Println(responseBody)

	return "Completed Document Kickoff", nil
}

func main() {
	runtime.Start(handleRequest)
}
