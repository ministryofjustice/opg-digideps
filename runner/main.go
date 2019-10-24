package main

import (
	"github.com/aws/aws-sdk-go/aws/awserr"
	"time"
	"context"
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/request"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	. "github.com/ministryofjustice/opg-digideps/runner/internal"
	"log"
	"os"
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

	ctx := aws.BackgroundContext()
	var cancelFn func()

	ctx, cancelFn = context.WithTimeout(ctx, time.Duration(timeout) * time.Second)

	defer cancelFn()

	input := &ecs.DescribeTasksInput{
		Cluster: runner.Task.ClusterArn,
		Tasks:   []*string{runner.Task.TaskArn},
	}

	err = runner.Svc.WaitUntilTasksStoppedWithContext(
		ctx,
		input,
		request.WithWaiterRequestOptions(func(r *request.Request) {
			for _, l := range cwLogs {
				l.PrintLogEvents()
			}
		}),
		request.WithWaiterDelay(request.ConstantWaiterDelay(delay)),
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

	runner.Update()

	exitCode := 0

	log.Printf("%s task stopped with status %s", taskName, *runner.GetStatus())

	for _, c := range runner.Task.Containers {
		log.Printf("%s container exited with code %d", *c.Name, *c.ExitCode)
		if *c.ExitCode > 0 {
			exitCode++
		}
	}

	os.Exit(exitCode)
}
