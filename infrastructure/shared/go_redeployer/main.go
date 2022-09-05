package main

import (
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/ecs"
	"github.com/aws/aws-lambda-go/lambda"
)

type RedeployEvent struct {
	Cluster string `json:"cluster"`
	Service string `json:"service"`
}

func HandleRequest(event RedeployEvent) (string, error) {
	sess, sessionErr := session.NewSession(&aws.Config{
		Region: aws.String("eu-west-1")},
	)

	if sessionErr != nil {
		return "An error occurred", sessionErr
	}

	ecsSvc := ecs.New(sess)

	_, deploymentErr := ecsSvc.UpdateService(&ecs.UpdateServiceInput{
		Cluster: aws.String(event.Cluster),
		Service: aws.String(event.Service),
		ForceNewDeployment: aws.Bool(true),
	})

	if deploymentErr != nil {
		return "An error occurred", deploymentErr
	}

	return fmt.Sprintf("Redeployed %s", event.Service), nil
}

func main() {
	lambda.Start(HandleRequest)
}
