package main

import (
	"encoding/json"
	"io/ioutil"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	"log"
	"os"
	"regexp"
	"strconv"
	"time"
)

func (t *Task) IsStopped() bool {
	return *t.task.LastStatus != "STOPPED"
}

func (t *Task) GetTaskID() string {
	return regexp.MustCompile("^.*/").ReplaceAllString(*t.task.TaskArn, "")
}

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
	timeOut  int
}

type Config struct {
	Sensitive bool
	Type []interface{}
	Value struct{
		AccountID      *string
		Cluster        *string
		SecurityGroups []*string
		Subnets        []*string
		TaskDefinition *string
	}
}

// TF_WORKSPACE must exist - CI
// Role - default to CI, allow overriding
// Task Definition Name - Command line or env var

// Terraform output

// Cluster - Terrform output, push to file
// Security Groups - Terrform output, push to file
// Subnets - Terrform output, push to file
// Account ID - terraform.tfvars.json


func main() {
	config := NewConfig("output.json", "sync")

	sess, _ := session.NewSession()
	creds := stscreds.NewCredentials(sess, fmt.Sprintf("arn:aws:iam::%s:role/operator", *config.Value.AccountID))

	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}

	task := Task{
		svc: ecs.New(sess, &awsConfig),
		input: &ecs.RunTaskInput{
			Cluster:    config.Value.Cluster,
			LaunchType: aws.String("FARGATE"),
			NetworkConfiguration: &ecs.NetworkConfiguration{
				AwsvpcConfiguration: &ecs.AwsVpcConfiguration{
					SecurityGroups: config.Value.SecurityGroups,
					Subnets:        config.Value.Subnets,
				},
			},
			TaskDefinition: config.Value.TaskDefinition,
			Overrides: &ecs.TaskOverride{
				ContainerOverrides: []*ecs.ContainerOverride{
					{
						Command: aws.StringSlice([]string{"./backup.sh"}),
						Name:    aws.String("sync"),
					},
				},
			},
		},
	}

	//run task
	task.Run()

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
		timeOut:  getEnvInt("DEPLOYER_TIMEOUT", "60"),
	}

	task.Update()

	for task.IsStopped() {
		task.Update()

		cwLog.PrintLogEvents()

		if poll.IsTimedOut() {
			log.Fatalf("Timed out after %v\n", poll.timeOut)
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

func getEnvInt(name string, defaultVar string) int {
	env, isSet := os.LookupEnv(name)

	if !isSet {
		env = defaultVar
	}

	intEnv, err := strconv.Atoi(env)

	if err != nil {
		log.Fatalln(err)
	}

	return intEnv
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
	return p.count*p.interval >= p.timeOut
}

func (p *Poll) Sleep() {
	time.Sleep(time.Duration(p.interval) * time.Second)
	p.count++
}

func NewConfig(fileName, taskName string) Config {
	byteValue, _ := ioutil.ReadFile(fileName)

	var configs map[string]Config
	err := json.Unmarshal(byteValue, &configs)
	
	if err != nil{
		log.Fatalln(err)
	}

	return configs[taskName]
}
