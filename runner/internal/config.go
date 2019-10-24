package internal

import (
	"log"
	"encoding/json"
	"io/ioutil"
	"github.com/aws/aws-sdk-go/service/ecs"
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
	Services struct {
		Sensitive bool
		Type      interface{}
		Value     *ecs.DescribeServicesInput
	}
}

func LoadConfig(configFile string) Config {
	byteValue, _ := ioutil.ReadFile(configFile)
	var config Config
	err := json.Unmarshal(byteValue, &config)
	if err != nil {
		log.Fatalln(err)
	}
	return config
}

