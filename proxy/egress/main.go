package main

import (
	"io"
	"log"
	"net"
	"net/http"
	"net/http/httputil"
	"net/url"
	"strings"
)

var allowList = []string{
	"example.com",
	"github.com",
	"amazonaws.com",
	"api",
}

// allowed returns true if the host ends with any allowed suffix
func allowed(host string) bool {
	for _, d := range allowList {
		if strings.HasSuffix(host, d) {
			return true
		}
	}
	return false
}

// targetAddress ensures host includes a port, defaulting to :80 or :443
func targetAddress(host, defaultPort string) string {
	if strings.Contains(host, ":") {
		return host
	}
	return net.JoinHostPort(host, defaultPort)
}

// handleHTTP handles standard HTTP requests (GET, POST, etc.)
func handleHTTP(w http.ResponseWriter, r *http.Request) {
	host := r.URL.Hostname()
	if host == "" {
		host = r.Host
	}

	log.Printf("[HTTP] URL: %s | Host: %s", r.URL, host)

	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("[BLOCKED HTTP] %s", host)
		return
	}

	log.Printf("[ALLOWED HTTP] %s", host)

	targetURL := &url.URL{
		Scheme: "http",
		Host:   host,
	}
	proxy := httputil.NewSingleHostReverseProxy(targetURL)
	proxy.Transport = &http.Transport{}
	proxy.ServeHTTP(w, r)
}

// handleHTTPS handles CONNECT tunneling (used for HTTPS)
func handleHTTPS(w http.ResponseWriter, r *http.Request) {
	host := strings.Split(r.Host, ":")[0]

	log.Printf("[HTTPS] CONNECT %s", host)

	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("[BLOCKED HTTPS] %s", host)
		return
	}

	log.Printf("[ALLOWED HTTPS] %s", host)

	target := targetAddress(r.Host, "443")
	destConn, err := net.Dial("tcp", target)
	if err != nil {
		http.Error(w, err.Error(), http.StatusServiceUnavailable)
		log.Printf("[ERROR] HTTPS dial failed: %v", err)
		return
	}

	w.WriteHeader(http.StatusOK)

	hj, ok := w.(http.Hijacker)
	if !ok {
		http.Error(w, "Hijacking not supported", http.StatusInternalServerError)
		return
	}

	clientConn, _, err := hj.Hijack()
	if err != nil {
		http.Error(w, err.Error(), http.StatusServiceUnavailable)
		return
	}
	defer clientConn.Close()

	go io.Copy(destConn, clientConn)
	io.Copy(clientConn, destConn)
}

func main() {
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodConnect {
			handleHTTPS(w, r)
		} else {
			handleHTTP(w, r)
		}
	})

	log.Println("🔌 Proxy listening on :3128")
	log.Fatal(http.ListenAndServe(":3128", nil))
}
