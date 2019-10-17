package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials/stscreds"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"github.com/aws/aws-sdk-go/service/ecs"
	"io/ioutil"
	"log"
	"os"
	"regexp"
	"strconv"
	"time"
)

type Config struct {
	Role  struct {
		Sensitive bool
		Type      interface{}
		Value     string
	}
	Tasks struct {
		Sensitive bool
		Type      []interface{}
		Value     map[string]*ecs.RunTaskInput
	}
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

func main() {
	flag.Usage = func() {
		fmt.Println("Usage: deployer -task <task>")
		flag.PrintDefaults()
	}
	var taskName string
	flag.String("help", "", "this help information")
	flag.StringVar(&taskName, "task", "", "task to run")
	flag.Parse()
	if taskName == "" {
		fmt.Println("Error: task name not set")
		flag.Usage()
	}

	config := LoadConfig()
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

func getEnvInt(name string, defaultVar string) int {
	env := getEnv(name, defaultVar)

	intEnv, err := strconv.Atoi(env)

	if err != nil {
		log.Fatalln(err)
	}

	return intEnv
}

func getEnv(name string, defaultVar string) string {
	env, isSet := os.LookupEnv(name)

	if !isSet {
		env = defaultVar
	}

	return env
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

func LoadConfig() Config {
	configFile := getEnv("CONFIG_FILE", "terraform.output.json")
	byteValue, _ := ioutil.ReadFile(configFile)
	var config Config
	err := json.Unmarshal(byteValue, &config)
	if err != nil {
		log.Fatalln(err)
	}
	return config
}
