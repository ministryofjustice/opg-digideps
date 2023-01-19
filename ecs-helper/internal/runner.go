package internal

import (
	"github.com/aws/aws-sdk-go/service/ecs"
	"log"
	"regexp"
)

type Runner struct {
	Svc   *ecs.ECS
	Task  *ecs.Task
	Input *ecs.RunTaskInput
}

func (r *Runner) Run() {
	tasksOutput, err := r.Svc.RunTask(r.Input)

	if err != nil {
		log.Fatalln(err)
	}

	r.Task = tasksOutput.Tasks[0]
}

func (r *Runner) Update() {
	describeTaskInput := &ecs.DescribeTasksInput{
		Cluster: r.Task.ClusterArn,
		Tasks:   []*string{r.Task.TaskArn},
	}

	describeTasksOutput, err := r.Svc.DescribeTasks(describeTaskInput)

	if err != nil {
		log.Fatalln(err)
	}

	r.Task = describeTasksOutput.Tasks[0]
}

func (r *Runner) GetStatus() string {
	r.Update()
	return *r.Task.LastStatus
}

type containerExitCode struct{
	Name string
	ExitCode int64
}

func (r *Runner) GetContainerExitCodes() []containerExitCode {
	r.Update()

	var containerExitCodes []containerExitCode

	for _, c := range r.Task.Containers {
		containerExitCodes = append(containerExitCodes,
			containerExitCode{
				Name: *c.Name,
				ExitCode: *c.ExitCode,
			},
		)
	}

	return containerExitCodes
}

func (r *Runner) GetTaskID() string {
	return regexp.MustCompile("^.*/").ReplaceAllString(*r.Task.TaskArn, "")
}

func (r *Runner) GetLogConfigurationOptions() map[string]*string {
	output, err := r.Svc.DescribeTaskDefinition(&ecs.DescribeTaskDefinitionInput{
		TaskDefinition: r.Input.TaskDefinition,
	})

	if err != nil {
		log.Fatalln(err)
	}

	return output.TaskDefinition.ContainerDefinitions[0].LogConfiguration.Options
}
