package main

import (
	"fmt"
	"io"
	"io/ioutil"
	"log"
	"ministryofjustice/opg-digideps/file-scanner/clamd"
	"net/http"
	"os"
	"strings"
	"time"
)

var opts map[string]string

func init() {
	log.SetOutput(ioutil.Discard)
}

// This is where the action happens
func scanHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	// POST takes the uploaded file(s) and saves it to disk
	case "POST":
		c := clamd.NewClamd(opts["CLAMD_PORT"])
		// Get the multipart reader for the request
		reader, err := r.MultipartReader()

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		// Copy each part to destination
		for {
			part, err := reader.NextPart()
			if err == io.EOF {
				break
			}

			// If part.FileName() is empty, skip this iteration
			if part.FileName() == "" {
				continue
			}

			fmt.Printf("%v Started scanning: %s\n", time.Now().Format(time.RFC3339), part.FileName())
			var abort chan bool

			// Do the scanning using clamav
			response, err := c.ScanStream(part, abort)
			for s := range response {
				w.Header().Set("Content-Type", "application/json; charset=utf-8")
				fileAccepted := "false"
				switch s.Status {
				case clamd.RES_OK:
					w.WriteHeader(http.StatusOK)
					fileAccepted = "true"
				case clamd.RES_FOUND:
					w.WriteHeader(http.StatusNotAcceptable)
				case clamd.RES_ERROR:
					w.WriteHeader(http.StatusBadRequest)
				case clamd.RES_PARSE_ERROR:
					w.WriteHeader(http.StatusPreconditionFailed)
				default:
					w.WriteHeader(http.StatusNotImplemented)
				}
				respJson := fmt.Sprintf("File Accepted: %s", fileAccepted)
				fmt.Fprint(w, respJson)
				fmt.Printf("%v Scan result for: %s, %s\n", time.Now().Format(time.RFC3339), part.FileName(), s.Status)
			}
		}
	default:
		w.WriteHeader(http.StatusMethodNotAllowed)
	}
}

// waitForClamD waits ubntil clamd is ready and prints a version to logs
func waitForClamD(port string, times int) {
	clamdTest := clamd.NewClamd(port)
	clamdTest.Ping()
	version, err := clamdTest.Version()

	if err != nil {
		if times < 30 {
			fmt.Printf("clamD not running, waiting times [%v]\n", times)
			time.Sleep(time.Second * 4)
			waitForClamD(port, times+1)
		} else {
			fmt.Printf("Error getting clamd version: %v\n", err)
			os.Exit(1)
		}
	} else {
		for version_string := range version {
			fmt.Printf("Clamd version: %#v\n", version_string.Raw)
		}
	}
}

// healthHandler returns OK if clamd is up and running
func healthHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case "GET":
		c := clamd.NewClamd(opts["CLAMD_PORT"])

		err := c.Ping()

		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
		} else {
			w.WriteHeader(http.StatusOK)
		}
	default:
		w.WriteHeader(http.StatusMethodNotAllowed)
	}
}

func main() {

	const (
		PORT = ":8080"
	)

	opts = make(map[string]string)

	for _, e := range os.Environ() {
		pair := strings.Split(e, "=")
		opts[pair[0]] = pair[1]
	}

	if opts["CLAMD_PORT"] == "" {
		opts["CLAMD_PORT"] = "tcp://localhost:3310"
	}

	fmt.Printf("Starting clamav rest bridge\n")
	fmt.Printf("Connecting to clamd on %v\n", opts["CLAMD_PORT"])
	waitForClamD(opts["CLAMD_PORT"], 1)

	fmt.Printf("Connected to clamd on %v\n", opts["CLAMD_PORT"])

	http.HandleFunc("/scan", scanHandler)
	http.HandleFunc("/health", healthHandler)

	// Start the HTTP server
	http.ListenAndServe(PORT, nil)
}
