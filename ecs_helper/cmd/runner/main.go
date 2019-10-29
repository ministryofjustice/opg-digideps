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
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	. "github.com/ministryofjustice/opg-digideps/ecs_helper/internal"
	"log"
	"os"
	"time"
)

func main() {
	flag.Usage = func() {
		fmt.Println("Usage: runner -task <task>")
		flag.PrintDefaults()
	}
	var taskName string
	var timeout int
	var configFile string

	flag.String("help", "", "this help information")
	flag.StringVar(&taskName, "task", "", "task to run")
	flag.IntVar(&timeout, "timeout", 120, "timeout for the task")
	flag.StringVar(&configFile, "config", "terraform.output.json", "config file for tasks")

	flag.Parse()
	if taskName == "" {
		fmt.Println("Error: task name not set")
		flag.Usage()
	}

	config := LoadConfig(configFile)
	sess, err := session.NewSession()
	if err != nil {
		log.Fatalln(err)
	}
	creds := stscreds.NewCredentials(sess, config.Role.Value)
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	runner := Runner{Svc: ecs.New(sess, &awsConfig), Input: config.Tasks.Value[taskName]}
	runner.Run()
	logConfigurationOptions := runner.GetLogConfigurationOptions()

	var cwLogs []Log

	for _, c := range runner.Task.Containers {
		cwLogs = append(cwLogs, Log{
			Svc: cloudwatchlogs.New(sess, &awsConfig),
			Input: &cloudwatchlogs.GetLogEventsInput{
				LogGroupName:  logConfigurationOptions["awslogs-group"],
				LogStreamName: aws.String(fmt.Sprintf("%s/%s/%s", *logConfigurationOptions["awslogs-stream-prefix"], *c.Name, runner.GetTaskID())),
				StartFromHead: aws.Bool(true),
			},
		})
	}

	delay := time.Second * 10
	ctx, cancelFn := context.WithTimeout(aws.BackgroundContext(), time.Duration(timeout) * time.Second)
	defer cancelFn()

	err = runner.Svc.WaitUntilTasksStoppedWithContext(
		ctx,
		&ecs.DescribeTasksInput{
			Cluster: runner.Task.ClusterArn,
			Tasks:   []*string{runner.Task.TaskArn},
		},
		request.WithWaiterRequestOptions(func(r *request.Request) {
			for _, l := range cwLogs {
				l.PrintLogEvents()
			}
		}),
		request.WithWaiterDelay(request.ConstantWaiterDelay(delay)),
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

	log.Printf("%s task stopped with status %s", taskName, runner.GetStatus())

	exitCode := 0
	for _, c := range runner.GetContainerExitCodes() {
		log.Printf("%s container exited with code %d", c.Name, c.ExitCode)
		if c.ExitCode > 0 {
			exitCode++
		}
	}
	os.Exit(exitCode)
}
