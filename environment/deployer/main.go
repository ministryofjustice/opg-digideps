package main

import (
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

func (t *Task) Update(ecsSvc *ecs.ECS) {
	t.task = describeTask(ecsSvc, t.task)
}

type Task struct {
	task *ecs.Task
}

func main() {
	var task Task

	cluster := "ddpb2944"
	securityGroups := []string{"sg-0ee40a8bbc67747e3"}
	subnets := []string{"subnet-d0b880a6", "subnet-a31455fb", "subnet-9ad4d1fe"}
	taskDefinition := "sync-ddpb2944"
	command := []string{"./backup.sh"}
	containerName := "sync"
	logGroupName := "ddpb2944"
	streamPrefix := "sync"
	delay := 5
	timeOut := getEnvInt("DEPLOYER_TIMEOUT")

	sess, _ := session.NewSession()
	creds := stscreds.NewCredentials(sess, "arn:aws:iam::248804316466:role/operator")
	awsConfig := aws.Config{Credentials: creds, Region: aws.String("eu-west-1")}
	ecsSvc := ecs.New(sess, &awsConfig)
	cloudwatchLogsSvc := cloudwatchlogs.New(sess, &awsConfig)

	//run task
	task.Run(ecsSvc, cluster, securityGroups, subnets, taskDefinition, command, containerName)

	//setup logs
	taskID := regexp.MustCompile("^.*/").ReplaceAllString(*task.task.TaskArn, "")

	cloudwatchLogsInput := &cloudwatchlogs.GetLogEventsInput{
		LogGroupName:  aws.String(logGroupName),
		LogStreamName: aws.String(fmt.Sprintf("%s/%s/%s", streamPrefix, containerName, taskID)),
		StartFromHead: aws.Bool(true),
	}

	count := 0

	task.Update(ecsSvc)
	for task.IsStopped() {
		task.Update(ecsSvc)
		cloudwatchLogsOutput, err := cloudwatchLogsSvc.GetLogEvents(cloudwatchLogsInput)

		if err != nil {
			log.Println(err)
		}

		cloudwatchLogsInput.NextToken = cloudwatchLogsOutput.NextForwardToken

		for _, event := range cloudwatchLogsOutput.Events {
			log.Println(*event.Message)
		}

		if count * delay >= timeOut {
			log.Fatalf("Timed out after %v\n", timeOut)
		} 

		time.Sleep(time.Duration(delay) * time.Second)
		count++
	}

	log.Printf("Container exited with code %d", *task.task.Containers[0].ExitCode)

	os.Exit(int(*task.task.Containers[0].ExitCode))
}

func (t *Task) Run(svc *ecs.ECS, cluster string, securityGroups []string, subnets []string, taskDefinition string, command []string, containerName string) {
	taskInput := &ecs.RunTaskInput{
		Cluster:    aws.String(cluster),
		LaunchType: aws.String("FARGATE"),
		NetworkConfiguration: &ecs.NetworkConfiguration{
			AwsvpcConfiguration: &ecs.AwsVpcConfiguration{
				SecurityGroups: aws.StringSlice(securityGroups),
				Subnets:        aws.StringSlice(subnets),
			},
		},
		TaskDefinition: aws.String(taskDefinition),
		Overrides: &ecs.TaskOverride{
			ContainerOverrides: []*ecs.ContainerOverride{
				{
					Command: aws.StringSlice(command),
					Name:    aws.String(containerName),
				},
			},
		},
	}

	tasksOutput, err := svc.RunTask(taskInput)

	if err != nil {
		log.Fatalln(err)
	}

	t.task = tasksOutput.Tasks[0]
}

func describeTask(svc *ecs.ECS, task *ecs.Task) *ecs.Task {
	describeTaskInput := &ecs.DescribeTasksInput{
		Cluster: task.ClusterArn,
		Tasks:   []*string{task.TaskArn},
	}

	describeTasksOutput, err := svc.DescribeTasks(describeTaskInput)

	if err != nil {
		log.Fatalln(err)
	}

	return describeTasksOutput.Tasks[0]
}

func getEnvInt(name string) int {
	env, err := strconv.Atoi(os.Getenv(name))

	if err != nil {
		log.Fatalf("Error getting %s: %v", name, err)
	}

	return env
}
