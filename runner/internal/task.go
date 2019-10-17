package internal

import (
	"log"
	"github.com/aws/aws-sdk-go/service/ecs"
	"regexp"

)

type Task struct {
	Svc   *ecs.ECS
	Task  *ecs.Task
	Input *ecs.RunTaskInput
}

func (t *Task) Run() {
	tasksOutput, err := t.Svc.RunTask(t.Input)

	if err != nil {
		log.Fatalln(err)
	}

	t.Task = tasksOutput.Tasks[0]
}

func (t *Task) Update() {
	describeTaskInput := &ecs.DescribeTasksInput{
		Cluster: t.Task.ClusterArn,
		Tasks:   []*string{t.Task.TaskArn},
	}

	describeTasksOutput, err := t.Svc.DescribeTasks(describeTaskInput)

	if err != nil {
		log.Fatalln(err)
	}

	t.Task = describeTasksOutput.Tasks[0]
}

func (t *Task) IsStopped() bool {
	return *t.Task.LastStatus != "STOPPED"
}

func (t *Task) GetTaskID() string {
	return regexp.MustCompile("^.*/").ReplaceAllString(*t.Task.TaskArn, "")
}

func (t *Task) GetContainerName() string {
	return *t.Task.Containers[0].Name
}

func (t *Task) GetLogConfigurationOptions() map[string]*string {
	output, err := t.Svc.DescribeTaskDefinition(&ecs.DescribeTaskDefinitionInput{
		TaskDefinition: t.Input.TaskDefinition,
	})

	if err != nil {
		log.Fatalln(err)
	}

	return output.TaskDefinition.ContainerDefinitions[0].LogConfiguration.Options
}
