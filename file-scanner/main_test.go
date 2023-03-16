package main

import (
	"bytes"
	"fmt"
	"io"
	"mime/multipart"
	"net"
	"net/http"
	"net/http/httptest"
	"os"
	"strings"
	"testing"
)

// ===== FILE CREATION FUNCTIONS ======
func createMultipartFormData(t *testing.T, fieldName, fileName string) (bytes.Buffer, *multipart.Writer) {
	var b bytes.Buffer
	var err error
	w := multipart.NewWriter(&b)
	var fw io.Writer
	file := mustOpen(fileName)
	if fw, err = w.CreateFormFile(fieldName, file.Name()); err != nil {
		t.Errorf("Error creating writer: %v", err)
	}
	if _, err = io.Copy(fw, file); err != nil {
		t.Errorf("Error with io.Copy: %v", err)
	}
	w.Close()
	return b, w
}

func mustOpen(f string) *os.File {
	r, err := os.Open(f)
	if err != nil {
		pwd, _ := os.Getwd()
		fmt.Println("PWD: ", pwd)
		panic(err)
	}
	return r
}

// ===== MOCK TCP SERVER FUNCTIONS ======
const (
	CONN_HOST = "localhost"
	CONN_PORT = "3333"
	CONN_TYPE = "tcp"
)

// Handles incoming requests.
func handleRequest(conn net.Conn) {
	// Make a buffer to hold incoming data.
	buf := make([]byte, 1024)
	// Read the incoming connection into the buffer.
	_, err := conn.Read(buf)
	if err != nil {
		fmt.Println("Error reading:", err.Error())
	}

	switch {
	case strings.Contains(string(buf), "EICAR-STANDARD"):
		conn.Write([]byte("stream: FOUND\n"))
	case strings.Contains(string(buf), "PING"):
		conn.Write([]byte("PONG\n"))
	default:
		conn.Write([]byte("stream: OK\n"))
	}

	// Close the connection when you're done with it.
	conn.Close()
}

func requestHandler(l net.Listener) {
	defer l.Close()
	for {
		// Listen for an incoming connection.
		conn, err := l.Accept()
		if err != nil {
			fmt.Println("Error accepting: ", err.Error())
			os.Exit(1)
		}
		// Handle connections in a new goroutine.
		go handleRequest(conn)
	}
}

func init() {
	// Listen for incoming connections.
	l, err := net.Listen(CONN_TYPE, CONN_HOST+":"+CONN_PORT)
	if err != nil {
		fmt.Println("Error listening:", err.Error())
		os.Exit(1)
	}

	fmt.Println("Listening on " + CONN_HOST + ":" + CONN_PORT)
	go requestHandler(l)
}

func Test_healthHandler(t *testing.T) {
	tests := []struct {
		name   string
		addr   string
		status int
	}{
		{
			name:   "Sucessful connection",
			addr:   ":3333",
			status: http.StatusOK,
		},
		{
			name:   "Unsucessful connection",
			addr:   ":3334",
			status: http.StatusInternalServerError,
		},
	}
	for _, tc := range tests {
		t.Run(tc.name, func(tt *testing.T) {
			opts = make(map[string]string)
			opts["CLAMD_PORT"] = "tcp://" + tc.addr

			// Create a request to pass to our handler. We don't have any query parameters for now, so we'll
			req, err := http.NewRequest("GET", "/health", nil)
			if err != nil {
				t.Fatal(err)
			}

			// We create a ResponseRecorder (which satisfies http.ResponseWriter) to record the response.
			rr := httptest.NewRecorder()
			handler := http.HandlerFunc(healthHandler)

			// Our handlers satisfy http.Handler, so we can call their ServeHTTP method
			// directly and pass in our Request and ResponseRecorder.
			handler.ServeHTTP(rr, req)

			// Check the status code is what we expect.
			if status := rr.Code; status != tc.status {
				t.Errorf("handler returned wrong status code: got %v want %v",
					status, tc.status)
			}
		})
	}
}

func Test_scanHandler(t *testing.T) {
	tests := []struct {
		name              string
		filename          string
		addr              string
		expected_status   int
		expected_response string
	}{
		{
			name:              "Acceptable file",
			filename:          "goodfile.txt",
			addr:              ":3333",
			expected_status:   http.StatusOK,
			expected_response: "File Accepted: true",
		},
		{
			name:              "Virus file",
			filename:          "eicar.test",
			addr:              ":3333",
			expected_status:   http.StatusNotAcceptable,
			expected_response: "File Accepted: false",
		},
	}
	for _, tc := range tests {
		t.Run(tc.name, func(tt *testing.T) {
			opts = make(map[string]string)
			opts["CLAMD_PORT"] = "tcp://" + tc.addr

			b, w := createMultipartFormData(t, "text", tc.filename)

			req, err := http.NewRequest("POST", "/scan", &b)
			if err != nil {
				return
			}
			// Content type, this will contain the boundary.
			req.Header.Set("Content-Type", w.FormDataContentType())

			// We create a ResponseRecorder (which satisfies http.ResponseWriter) to record the response.
			rr := httptest.NewRecorder()
			handler := http.HandlerFunc(scanHandler)

			// directly and pass in our Request and ResponseRecorder.
			handler.ServeHTTP(rr, req)

			// Check the status code is what we expect.
			if status := rr.Code; status != tc.expected_status {
				t.Errorf("handler returned wrong status code: got %v want %v",
					status, tc.expected_status)
			}

			// Check the response body is what we expect.
			if rr.Body.String() != string(tc.expected_response) {
				t.Errorf("handler returned unexpected body: got %v want %v",
					rr.Body.String(), tc.expected_response)
			}
		})
	}
}
