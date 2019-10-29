package main

import (
	"context"
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/awserr"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/request"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/ecs"
	. "github.com/ministryofjustice/opg-digideps/ecs_helper/internal"
	"log"
	"time"
)

func main() {
	flag.Usage = func() {
		fmt.Println("Usage: stabilizer")
		flag.PrintDefaults()
	}
	var timeout int
	var configFile string

	flag.String("help", "", "this help information")
	flag.IntVar(&timeout, "timeout", 300, "timeout for the services to stabilize")
	flag.StringVar(&configFile, "config", "terraform.output.json", "config file for services")

	config := LoadConfig(configFile)

	sess, err := session.NewSession()
	if err != nil {
		log.Fatalln(err)
	}
	creds := stscreds.NewCredentials(sess, config.Role.Value)
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	ecsSvc := ecs.New(sess, &awsConfig)

	delay := time.Second * 10
	ctx, cancelFn := context.WithTimeout(aws.BackgroundContext(), time.Duration(timeout) * time.Second)
	defer cancelFn()

	start := time.Now()
	err = ecsSvc.WaitUntilServicesStableWithContext(
		ctx,
		config.Services.Value,
		request.WithWaiterDelay(request.ConstantWaiterDelay(delay)),
		request.WithWaiterRequestOptions(func(r *request.Request) {
			log.Printf("waited %v for services to stabilize...\n", time.Since(start).Round(time.Second))
		}),
		request.WithWaiterMaxAttempts(0),
	)

	if err != nil {
		if aerr, ok := err.(awserr.Error); ok {
			switch aerr.Code() {
			case request.CanceledErrorCode:
				log.Fatalln("Timeout exceeded")
			default:
				log.Fatalln(aerr)
			}
		} else {
			log.Fatalln(err)
		}
	}
}
