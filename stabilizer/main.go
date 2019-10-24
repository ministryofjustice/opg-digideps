package main

import (
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/request"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/ecs"
	. "github.com/ministryofjustice/opg-digideps/stabilizer/internal"
	"time"
)
import "log"

func main() {
	var configFile string
	flag.StringVar(&configFile, "config", "terraform.output.json", "config file for tasks")
	config := LoadConfig(configFile)

	sess, err := session.NewSession()
	if err != nil {
		log.Fatalln(err)
	}
	creds := stscreds.NewCredentials(sess, config.Role.Value)
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	ecsSvc := ecs.New(sess, &awsConfig)

	start := time.Now()
	err = ecsSvc.WaitUntilServicesInactiveWithContext(
		aws.BackgroundContext(),
		config.Services.Value,
		request.WithWaiterDelay(request.ConstantWaiterDelay(time.Second * 10)),
		request.WithWaiterRequestOptions(func(r *request.Request) {
			fmt.Printf("waited %v for services to stabilize...\n", time.Since(start).Round(time.Second))
		}),
	)
	if err != nil {
		log.Fatalln(err)
	}

}
