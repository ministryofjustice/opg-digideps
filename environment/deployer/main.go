package main

import (
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	// "github.com/aws/aws-sdk-go/service/sts"
	"github.com/aws/aws-sdk-go/service/ecs"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
)

func main() {
	sess, _ := session.NewSession()
	creds := stscreds.NewCredentials(sess, "arn:aws:iam::248804316466:role/operator")

	// stsSvc := sts.New(sess, &aws.Config{Credentials: creds})
	// output, _ := stsSvc.GetCallerIdentity(&sts.GetCallerIdentityInput{})
	// fmt.Println(output)

	svc := ecs.New(sess, &aws.Config{Credentials: creds, Region: aws.String("eu-west-1")})

	taskInput := &ecs.RunTaskInput{
		Cluster: aws.String("ddpb2944"),
		TaskDefinition: aws.String("sync-ddpb2944"),
	}

	output, err := svc.RunTask(taskInput)

	if err != nil {
		fmt.Println(err)
	}

	fmt.Println(output)

}