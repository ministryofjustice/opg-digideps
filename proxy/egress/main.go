package main

import (
	"io"
	"log"
	"net"
	"net/http"
	"net/http/httputil"
	"strings"
)

var allowList = []string{
	".example.com",
	".github.com",
	".amazonaws.com",
}

func allowed(host string) bool {
	for _, d := range allowList {
		if strings.HasSuffix(host, d) {
			return true
		}
	}
	return false
}

func handleHTTP(w http.ResponseWriter, r *http.Request) {
	host := r.URL.Hostname()
	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("BLOCKED HTTP: %s", host)
	}

	log.Printf("ALLOWED HTTP: %s", host)
	proxy := httputil.NewSingleHostReverseProxy(r.URL)
	proxy.Transport = &http.Transport{}
	proxy.ServeHTTP(w, r)
}

func handleHTTPS(w http.ResponseWriter, r *http.Request) {
	host := strings.Split(r.Host, ":")[0]
	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("BLOCKED HTTPS: %s", host)
	}

	log.Printf("ALLOWED HTTPS: %s", host)
	destConn, err := net.Dial("tcp", r.Host)
	if err != nil {
		http.Error(w, err.Error(), http.StatusServiceUnavailable)
		return
	}
	w.WriteHeader(http.StatusOK)
	hijacker, _ := w.(http.Hijacker)
	clientConn, _, _ := hijacker.Hijack()
	go io.Copy(destConn, clientConn)
	go io.Copy(clientConn, destConn)
}

func main() {
	http.HandleFunc("/", handleHTTP)
	http.HandleFunc("/connect", handleHTTPS)

	server := &http.Server{
		Addr: ":3128",
		Handler: http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
			if r.Method == http.MethodConnect {
				handleHTTPS(w, r)
			} else {
				handleHTTP(w, r)
			}
		}),
	}
	log.Println("Proxy listening on :3128")
	log.Fatal(server.ListenAndServe())
}
