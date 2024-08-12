package main

import (
	"flag"
	"fmt"
	"os"
	"strings"

	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/ecs"
	"github.com/aws/aws-sdk-go/service/lambda"
	"github.com/aws/aws-sdk-go/service/rds"
)

func main() {
	// Define and parse command-line flags
	action := flag.String("action", "", "Action to perform: OFF or ON")
	flag.Parse()

	allowedEnvironments := []string{"preproduction", "training", "integration", "development"}
	environment := strings.ToLower(os.Getenv("ENVIRONMENT"))

	// Check if environment variable is not equal to any of the allowed environments
	validEnv := false
	for _, env := range allowedEnvironments {
		if environment == env {
			validEnv = true
		}
	}
	if !validEnv {
		fmt.Println("Environment is not one of the allowed environments. Returning...")
		return
	}

	rdsClusterID := fmt.Sprintf("api-%s", environment)
	ecsClusterName := environment

	if environment == "" || *action == "" {
		fmt.Println("ENVIRONMENT env var and ACTION argument must be set")
		flag.Usage()
		return
	}

	sess, err := session.NewSession(&aws.Config{
		Region: aws.String(os.Getenv("AWS_REGION")),
	})
	if err != nil {
		fmt.Println("Error creating session:", err)
		return
	}

	switch *action {
	case "OFF":
		turnOff(sess, rdsClusterID, ecsClusterName)
	case "ON":
		turnOn(sess, rdsClusterID, ecsClusterName)
	default:
		fmt.Println("action flag must be either 'OFF' or 'ON'")
	}
}

func turnOff(sess *session.Session, rdsClusterID string, ecsClusterName string) {
	rdsSvc := rds.New(sess)
	_, err := rdsSvc.StopDBCluster(&rds.StopDBClusterInput{
		DBClusterIdentifier: aws.String(rdsClusterID),
	})
	if err != nil {
		if strings.Contains(err.Error(), "InvalidDBClusterStateFault") {
			fmt.Println("RDS cluster is already stopped")
		} else {
			fmt.Println("Error stopping RDS cluster:", err)
			return
		}
	}
	fmt.Printf("Stopping RDS cluster: %s\n", rdsClusterID)

	ecsSvc := ecs.New(sess)
	listServicesInput := &ecs.ListServicesInput{
		Cluster: aws.String(ecsClusterName),
	}
	listServicesOutput, err := ecsSvc.ListServices(listServicesInput)
	if err != nil {
		fmt.Println("Error listing ECS services:", err)
		return
	}

	for _, serviceArn := range listServicesOutput.ServiceArns {
		_, err := ecsSvc.UpdateService(&ecs.UpdateServiceInput{
			Cluster:            aws.String(ecsClusterName),
			Service:            serviceArn,
			DesiredCount:       aws.Int64(0),
			ForceNewDeployment: aws.Bool(true),
		})
		if err != nil {
			fmt.Printf("Error updating service %s: %s\n", *serviceArn, err)
			continue
		}
		fmt.Printf("Set desired count to 0 for service: %s\n", *serviceArn)
	}

	lambdaSvc := lambda.New(sess)
	_, err = lambdaSvc.UpdateFunctionConfiguration(&lambda.UpdateFunctionConfigurationInput{
		FunctionName: aws.String("slack-notifier"),
		Environment: &lambda.Environment{
			Variables: map[string]*string{
				"PAUSE_NOTIFICATIONS": aws.String("1"),
			},
		},
	})
	if err != nil {
		panic(err)
	}
}

func turnOn(sess *session.Session, rdsClusterID string, ecsClusterName string) {
	rdsSvc := rds.New(sess)
	_, err := rdsSvc.StartDBCluster(&rds.StartDBClusterInput{
		DBClusterIdentifier: aws.String(rdsClusterID),
	})
	if err != nil {
		if strings.Contains(err.Error(), "InvalidDBClusterStateFault") {
			fmt.Println("RDS cluster is already started")
		} else {
			fmt.Println("Error starting RDS cluster:", err)
			return
		}
	}
	fmt.Printf("Starting RDS cluster: %s\n", rdsClusterID)

	ecsSvc := ecs.New(sess)
	listServicesInput := &ecs.ListServicesInput{
		Cluster: aws.String(ecsClusterName),
	}
	listServicesOutput, err := ecsSvc.ListServices(listServicesInput)
	if err != nil {
		fmt.Println("Error listing ECS services:", err)
		return
	}

	for _, serviceArn := range listServicesOutput.ServiceArns {
		describeServicesInput := &ecs.DescribeServicesInput{
			Cluster:  aws.String(ecsClusterName),
			Services: []*string{serviceArn},
		}
		describeServicesOutput, err := ecsSvc.DescribeServices(describeServicesInput)
		if err != nil {
			fmt.Printf("Error describing service %s: %s\n", *serviceArn, err)
			continue
		}

		for _, service := range describeServicesOutput.Services {
			if !strings.Contains(*service.ServiceName, "checklist-sync") && !strings.Contains(*service.ServiceName, "document-sync") && !strings.Contains(*service.ServiceName, "mock-sirius") {
				_, err := ecsSvc.UpdateService(&ecs.UpdateServiceInput{
					Cluster:            aws.String(ecsClusterName),
					Service:            serviceArn,
					DesiredCount:       aws.Int64(1),
					ForceNewDeployment: aws.Bool(true),
				})
				if err != nil {
					fmt.Printf("Error updating service %s: %s\n", *serviceArn, err)
					continue
				}
				fmt.Printf("Set desired count to 1 for service: %s\n", *serviceArn)
			} else {
				fmt.Printf("Skipping service: %s\n", *service.ServiceName)
			}
		}
	}

	lambdaSvc := lambda.New(sess)
	_, err = lambdaSvc.UpdateFunctionConfiguration(&lambda.UpdateFunctionConfigurationInput{
		FunctionName: aws.String("slack-notifier"),
		Environment: &lambda.Environment{
			Variables: map[string]*string{
				"PAUSE_NOTIFICATIONS": aws.String("0"),
			},
		},
	})
	if err != nil {
		panic(err)
	}
}
