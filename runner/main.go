package main

import (
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	app "github.com/ministryofjustice/opg-digideps/runner/internal"
	"log"
	"os"
)

func main() {
	flag.Usage = func() {
		fmt.Println("Usage: deployer -task <task>")
		flag.PrintDefaults()
	}
	var taskName string
	var timeout int
	var configFile string

	flag.String("help", "", "this help information")
	flag.StringVar(&taskName, "task", "", "task to run")
	flag.IntVar(&timeout, "timeout", 60, "timeout for the task")
	flag.StringVar(&configFile, "config", "terraform.output.json", "config file for tasks")

	flag.Parse()
	if taskName == "" {
		fmt.Println("Error: task name not set")
		flag.Usage()
	}

	config := app.LoadConfig(configFile)
	//TODO: handle this error
	sess, _ := session.NewSession()
	creds := stscreds.NewCredentials(sess, config.Role.Value)
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	task := app.Task{Svc: ecs.New(sess, &awsConfig), Input: config.Tasks.Value[taskName]}
	task.Run()

	//TODO: refactor - this log setup feels messy
	logConfigurationOptions := task.GetLogConfigurationOptions()

	var cwLogs []app.Log

	for _, c := range task.Task.Containers {
		cwLogs = append(cwLogs, app.Log{
			Svc: cloudwatchlogs.New(sess, &awsConfig),
			Input: &cloudwatchlogs.GetLogEventsInput{
				LogGroupName:  logConfigurationOptions["awslogs-group"],
				LogStreamName: aws.String(fmt.Sprintf("%s/%s/%s", *logConfigurationOptions["awslogs-stream-prefix"], *c.Name, task.GetTaskID())),
				StartFromHead: aws.Bool(true),
			},
		})
	}

	poll := app.Poll{
		Count:    0,
		Interval: 5,
		Timeout:  timeout,
	}

	task.Update()

	for task.IsStopped() {
		task.Update()

		for _, l := range cwLogs {
			l.PrintLogEvents()
		}

		if poll.IsTimedOut() {
			log.Fatalf("Timed out after %v\n", poll.Timeout)
		}

		poll.Sleep()
	}

	log.Printf("Container exited with code %d", *task.Task.Containers[0].ExitCode)

	os.Exit(int(*task.Task.Containers[0].ExitCode))
}
