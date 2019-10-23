package main

import (
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	. "github.com/ministryofjustice/opg-digideps/runner/internal"
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

	poll := Poll{
		Count:    0,
		Interval: 5,
		Timeout:  timeout,
	}

	runner.Update()

	for runner.IsStopped() {
		runner.Update()

		for _, l := range cwLogs {
			l.PrintLogEvents()
		}

		if poll.IsTimedOut() {
			log.Fatalf("Timed out after %v\n", poll.Timeout)
		}

		poll.Sleep()
	}

	exitCode := 0

	log.Printf("%s task stopped with status %s", taskName, *runner.Task.LastStatus)

	for _, c := range runner.Task.Containers {
		log.Printf("%s container exited with code %d", *c.Name, *c.ExitCode)
		if *c.ExitCode > 0 {
			exitCode ++
		}
	}
	
	os.Exit(exitCode)
}
