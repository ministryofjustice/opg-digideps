package main

import (
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	"log"
	"os"
	"regexp"
	"time"
	"github.com/ministryofjustice/opg-digideps/runner/config"
)

type Task struct {
	svc   *ecs.ECS
	task  *ecs.Task
	input *ecs.RunTaskInput
}

type Log struct {
	svc   *cloudwatchlogs.CloudWatchLogs
	input *cloudwatchlogs.GetLogEventsInput
}

type Poll struct {
	count    int
	interval int
	timeout  int
}

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

	config := config.LoadConfig(configFile)
	//TODO: handle this error
	sess, _ := session.NewSession()
	creds := stscreds.NewCredentials(sess, config.Role.Value)
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	task := Task{svc: ecs.New(sess, &awsConfig), input: config.Tasks.Value[taskName]}
	task.Run()

	//TODO: refactor - this log setup feels messy
	logConfigurationOptions := task.GetLogConfigurationOptions()

	cwLog := Log{
		svc: cloudwatchlogs.New(sess, &awsConfig),
		input: &cloudwatchlogs.GetLogEventsInput{
			LogGroupName:  logConfigurationOptions["awslogs-group"],
			LogStreamName: aws.String(fmt.Sprintf("%s/%s/%s", *logConfigurationOptions["awslogs-stream-prefix"], task.GetContainerName(), task.GetTaskID())),
			StartFromHead: aws.Bool(true),
		},
	}

	poll := Poll{
		count:    0,
		interval: 5,
		timeout:  timeout,
	}

	task.Update()

	for task.IsStopped() {
		task.Update()

		cwLog.PrintLogEvents()

		if poll.IsTimedOut() {
			log.Fatalf("Timed out after %v\n", poll.timeout)
		}

		poll.Sleep()
	}

	log.Printf("Container exited with code %d", *task.task.Containers[0].ExitCode)

	os.Exit(int(*task.task.Containers[0].ExitCode))
}

func (t *Task) Run() {
	tasksOutput, err := t.svc.RunTask(t.input)

	if err != nil {
		log.Fatalln(err)
	}

	t.task = tasksOutput.Tasks[0]
}

func (t *Task) Update() {
	describeTaskInput := &ecs.DescribeTasksInput{
		Cluster: t.task.ClusterArn,
		Tasks:   []*string{t.task.TaskArn},
	}

	describeTasksOutput, err := t.svc.DescribeTasks(describeTaskInput)

	if err != nil {
		log.Fatalln(err)
	}

	t.task = describeTasksOutput.Tasks[0]
}

func (t *Task) IsStopped() bool {
	return *t.task.LastStatus != "STOPPED"
}

func (t *Task) GetTaskID() string {
	return regexp.MustCompile("^.*/").ReplaceAllString(*t.task.TaskArn, "")
}

func (t *Task) GetContainerName() string {
	return *t.task.Containers[0].Name
}

func (t *Task) GetLogConfigurationOptions() map[string]*string {
	output, err := t.svc.DescribeTaskDefinition(&ecs.DescribeTaskDefinitionInput{
		TaskDefinition: t.input.TaskDefinition,
	})

	if err != nil {
		log.Fatalln(err)
	}

	return output.TaskDefinition.ContainerDefinitions[0].LogConfiguration.Options
}

func (l *Log) PrintLogEvents() {
	cloudwatchLogsOutput, err := l.svc.GetLogEvents(l.input)

	if err != nil {
		log.Println(err)
	}

	l.input.NextToken = cloudwatchLogsOutput.NextForwardToken

	for _, event := range cloudwatchLogsOutput.Events {
		log.Println(*event.Message)
	}
}

func (p *Poll) IsTimedOut() bool {
	return p.count*p.interval >= p.timeout
}

func (p *Poll) Sleep() {
	time.Sleep(time.Duration(p.interval) * time.Second)
	p.count++
}


