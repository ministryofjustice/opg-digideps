package main

import (
	"encoding/json"
	"log"
	"net/http"
	"time"
)

// regServiceResponse
type regServiceResponse struct {
	Name      string
	Timestamp time.Time
}

func regService(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	// Setup the data struct to be return
	data := regServiceResponse{
		Name:      "Go API Test",
		Timestamp: time.Now(),
	}

	// Marshal just returns the JSON encoding
	json, err := json.Marshal(data)
	if err != nil {
		log.Fatal(err)
	}

	// Print the JSON to the endpoint
	w.Write(json)
}

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("/", regService)

	log.Println("Starting Go API service on :8080")
	err := http.ListenAndServe(":8080", mux)
	log.Fatal(err)
}
