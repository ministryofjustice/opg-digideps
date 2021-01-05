package main

import (
	"fmt"
	"net/http"
	"time"
	"flag"
)

func makeRequests(url string, chStatus chan<-int, batchSize int) {
  for z := 0; z < batchSize; z++ {
    	req, _ := http.NewRequest(http.MethodGet, url, nil)
    	req.Header.Set("Content-Type", "application/json")
    	res, err := http.DefaultClient.Do(req)
    	if err != nil {
    		fmt.Printf("failed to call remote service: (%v)\n", err)
    	}
    	defer res.Body.Close()
    	chStatus <- res.StatusCode
  }
}

func main() {
	baseUrl := flag.String("base_url", "ddpb3833.complete-deputy-report.service.gov.uk", "a string")
	urlSuffix := flag.String("url_suffix", "manage/availability", "a string")
	batchSize := flag.Int("batch_size", 50, "an int")
	numberOfBatches := flag.Int("number_of_batches", 100, "an int")
	waitBetweenBatches := flag.Int("wait_between_batches", 1, "an int")
	flag.Parse()

	url := "https://" + *baseUrl + "/" + *urlSuffix
	var respStatus int
	chStatus := make(chan int)

  for i := 0; i < *numberOfBatches; i++ {
    fmt.Printf("Processing batch %v of %v\n\n", i+1, *numberOfBatches)
    go makeRequests(url, chStatus, *batchSize)
    for z := 0; z < *batchSize; z++ {
      respStatus = <- chStatus
      fmt.Println(respStatus)
    }
    time.Sleep(time.Duration(*waitBetweenBatches)*time.Second )
  }
}
