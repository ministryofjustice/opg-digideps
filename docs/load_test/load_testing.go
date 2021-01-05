package main

import (
	"fmt"
	// "io/ioutil"
	"net/http"
	// "path/filepath"
// 	"time"
	"flag"
)

func makeRequest(url string, chStatus chan<-int) {

	req, _ := http.NewRequest(http.MethodGet, url, nil)
	req.Header.Set("Content-Type", "application/json")

	// start := time.Now()
	res, err := http.DefaultClient.Do(req)
	if err != nil {
		fmt.Printf("failed to call remote service: (%v)\n", err)
	}
	// secs := time.Since(start).Seconds()

	defer res.Body.Close()
	// respBody, _ := ioutil.ReadAll(res.Body)

	chStatus <- res.StatusCode
}

// func makeRequestBasic(url string) {
//
// 	req, _ := http.NewRequest(http.MethodGet, url, nil)
// 	req.Header.Set("Content-Type", "application/json")
//
// 	res, err := http.DefaultClient.Do(req)
// 	if err != nil {
// 		fmt.Printf("failed to call remote service: (%v)\n", err)
// 	}
// 	fmt.Println(res.StatusCode)
// }

func main() {
	baseUrl := flag.String("base_url", "complete-deputy-report.service.gov.uk", "a string")
	urlSuffix := flag.String("url_suffix", "manage/availability", "a string")
// 	batchSize := flag.Int("batch_size", 1, "an int")
// 	numberOfBatches := flag.Int("number_of_batches", 1, "an int")
// 	waitBetweenBatches := flag.Int("wait_between_batches", 1, "an int")
	flag.Parse()

	url := "https://" + *baseUrl + "/" + *urlSuffix

  makeRequestBasic(url)

// 	chStatus := make(chan int)
//
// 	fmt.Printf("===== Starting %v batches of %v requests with a wait time of %v secs between each of them =====\n\n", *numberOfBatches, *batchSize, *waitBetweenBatches)
// 	start := time.Now()
// 	for i := 0; i < *numberOfBatches; i++ {
// 		fmt.Printf("Processing batch %v of %v\n\n", i+1, *numberOfBatches)
// 		makeRequest(url, chStatus)
// 		time.Sleep(time.Duration(*waitBetweenBatches)*time.Second )
// 	}
//
// 	for z := 0; z < (*numberOfBatches * *batchSize); z++ {
// 		fmt.Println(<-chStatus)
// 	}
//
// 	fmt.Printf("===== Total run finished in %.2fs =====\n\n", time.Since(start).Seconds())
}
