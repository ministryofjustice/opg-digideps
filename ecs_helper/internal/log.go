package internal

import (
	"fmt"
	"github.com/aws/aws-sdk-go/aws/awserr"
	"github.com/aws/aws-sdk-go/service/cloudwatchlogs"
	"log"
)

type Log struct {
	Svc   *cloudwatchlogs.CloudWatchLogs
	Input *cloudwatchlogs.GetLogEventsInput
}

func (l *Log) PrintLogEvents() {
	cloudwatchLogsOutput, err := l.Svc.GetLogEvents(l.Input)

	if err != nil {
		if aerr, ok := err.(awserr.Error); ok {
			switch aerr.Code() {
			case cloudwatchlogs.ErrCodeResourceNotFoundException:
				log.Println("Waiting for log stream to start...")
				err = nil
			default:
				log.Fatalln(aerr)
			}
		} else {
			log.Fatalln(err)
		}
	}

	l.Input.NextToken = cloudwatchLogsOutput.NextForwardToken

	for _, event := range cloudwatchLogsOutput.Events {
		log.Println(fmt.Sprintf("%s: %v", *l.Input.LogStreamName, *event.Message))
	}
}
