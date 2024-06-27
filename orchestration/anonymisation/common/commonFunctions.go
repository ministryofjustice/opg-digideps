package common

import (
	"fmt"
	"os"
	"runtime"
	"strings"
	"time"
)

func CheckError(err error) {
	if err != nil {
		panic(err)
	}
}

func LogInformation(function string, message string) {
	currentTime := time.Now()

	timeFormatted := currentTime.Format("15:04.000")
	fmt.Printf("%s - %s - %s\n\n", timeFormatted, function, message)
}

func GetCurrentFuncName() string {
	pc, _, _, _ := runtime.Caller(1)
	input := runtime.FuncForPC(pc).Name()
	// Split the input string on "."
	parts := strings.Split(input, ".")

	// Check if there are at least two elements after splitting
	if len(parts) >= 2 {
		// Access the second element (index 1)
		secondElement := parts[1]
		return secondElement
	} else {
		return input
	}
}

func GetEnvWithDefault(env string, defaultValue string) string {
	envValue := os.Getenv(env)

	// Check if the environment variable is set
	if envValue != "" {
		return envValue
	} else {
		return defaultValue
	}
}

func ConvertToBool(value int) bool {
	var valueAsBool bool
	if value == 0 {
		valueAsBool = false
	} else if value == 1 {
		valueAsBool = true
	} else {
		fmt.Println("Invalid value for boolean conversion - default to False")
		return false
	}

	return valueAsBool
}

func RemoveDuplicateStr(strSlice []string) []string {
	allKeys := make(map[string]bool)
	list := []string{}
	for _, item := range strSlice {
		if _, value := allKeys[item]; !value {
			allKeys[item] = true
			list = append(list, item)
		}
	}
	return list
}
