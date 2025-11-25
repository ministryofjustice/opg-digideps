package main

import (
	"context"
	"io"
	"log"
	"net"
	"net/http"
	"net/url"
	"strings"
	"time"
)

var allowList = []string{
	"example.com",
	"github.com",
	"amazonaws.com",
	"api",
}

func main() {
	addr := ":3128"
	server := &http.Server{
		Addr:         addr,
		Handler:      http.HandlerFunc(proxyHandler),
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 15 * time.Second,
		IdleTimeout:  60 * time.Second,
	}
	log.Printf("Proxy listening on %s", addr)

	// Graceful shutdown hook (optional)
	go func() {
		// you can add signal handling here to call server.Shutdown(ctx)
	}()

	if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
		log.Fatalf("server error: %v", err)
	}
}

func proxyHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodConnect:
		log.Printf("[CONNECT] Host: %s | Remote: %s", r.Host, r.RemoteAddr)
		handleHTTPS(w, r)
	default:
		log.Printf("[HTTP] %s %s | Host: %s | Remote: %s", r.Method, r.URL.String(), r.Host, r.RemoteAddr)
		handleHTTP(w, r)
	}
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

// handleHTTPS handles tunneling for CONNECT requests (HTTPS).
func handleHTTPS(w http.ResponseWriter, r *http.Request) {
	// Hijack the connection to get the raw net.Conn
	hj, ok := w.(http.Hijacker)
	if !ok {
		http.Error(w, "proxy does not support hijacking", http.StatusInternalServerError)
		return
	}
	clientConn, clientRw, err := hj.Hijack()
	if err != nil {
		log.Printf("hijack error: %v", err)
		return
	}
	defer func() {
		_ = clientConn.Close()
	}()

	targetAddr := r.Host

	host := strings.Split(targetAddr, ":")[0]
	log.Printf("[HTTPS] CONNECT %s", host)

	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("[HTTPS] Blocked: %s", host)
		return
	}

	// Dial the target host (typically host:443)
	if _, _, err := net.SplitHostPort(targetAddr); err != nil {
		// If no port provided, default to 443 for HTTPS
		targetAddr = net.JoinHostPort(targetAddr, "443")
	}

	log.Printf("[ALLOWED HTTPS] %s", host)

	dialer := &net.Dialer{Timeout: 10 * time.Second}
	targetConn, err := dialer.DialContext(r.Context(), "tcp", targetAddr)
	if err != nil {
		log.Printf("dial target error (%s): %v", targetAddr, err)
		// Send a failure response to client
		_, _ = clientRw.WriteString("HTTP/1.1 502 Bad Gateway\r\n\r\n")
		_ = clientRw.Flush()
		return
	}
	// We will close this after copy completes
	defer func() {
		_ = targetConn.Close()
	}()

	// Send 200 Connection Established to client to start the tunnel
	_, _ = clientRw.WriteString("HTTP/1.1 200 Connection Established\r\n\r\n")
	_ = clientRw.Flush()

	// Bi-directional copy between clientConn and targetConn
	// Use goroutines to copy both directions; when one finishes, close the other.
	errCh := make(chan error, 2)

	go func() {
		_, err := io.Copy(targetConn, clientConn)
		errCh <- err
	}()

	go func() {
		_, err := io.Copy(clientConn, targetConn)
		errCh <- err
	}()

	// Wait for either direction to finish
	err1 := <-errCh
	err2 := <-errCh
	if err1 != nil {
		log.Printf("[CONNECT] upstream copy closed: %v", err1)
	}
	if err2 != nil {
		log.Printf("[CONNECT] downstream copy closed: %v", err2)
	}
	log.Printf("[CONNECT] Tunnel closed: %s", r.Host)
}

// handleHTTP proxies plain HTTP requests (GET/POST/etc.) without tunneling.
func handleHTTP(w http.ResponseWriter, r *http.Request) {
	// Make sure the URL is absolute for proxying
	if !r.URL.IsAbs() {
		// For proxies, clients should send absolute URLs; if not, reconstruct
		// using Host header + scheme guess (default http)
		u := &url.URL{
			Scheme: "http",
			Host:   r.Host,
			Path:   r.URL.Path,
		}
		u.RawQuery = r.URL.RawQuery
		r.URL = u
	}

	host := r.URL.Hostname()
	if host == "" {
		host = r.Host
	}

	log.Printf("[HTTP] URL: %s | Host: %s", r.URL, host)

	if !allowed(host) {
		http.Error(w, "Blocked by domain policy", http.StatusForbidden)
		log.Printf("[HTTP] Blocked: %s", host)
		return
	}

	// Create a new request to the target
	outReq := r.Clone(context.Background())
	// Per RFC 7230, proxies should remove hop-by-hop headers
	removeHopByHopHeaders(outReq.Header)
	outReq.RequestURI = "" // must be empty for http.Client.Do

	transport := &http.Transport{
		Proxy:                 nil, // don’t chain to another proxy
		ForceAttemptHTTP2:     false,
		DisableKeepAlives:     false,
		TLSHandshakeTimeout:   10 * time.Second,
		ResponseHeaderTimeout: 15 * time.Second,
		ExpectContinueTimeout: 1 * time.Second,
	}

	resp, err := transport.RoundTrip(outReq)
	if err != nil {
		log.Printf("[HTTP] roundtrip error for %s: %v", outReq.URL.String(), err)
		http.Error(w, "Bad Gateway", http.StatusBadGateway)
		return
	}
	defer resp.Body.Close()

	// Copy response headers/status/body back to the client
	copyHeaders(w.Header(), resp.Header)
	w.WriteHeader(resp.StatusCode)
	n, copyErr := io.Copy(w, resp.Body)
	log.Printf("[HTTP] %s -> %d (%d bytes)", outReq.URL.String(), resp.StatusCode, n)
	if copyErr != nil {
		log.Printf("[HTTP] client write error: %v", copyErr)
	}
}

func removeHopByHopHeaders(h http.Header) {
	// Hop-by-hop headers per RFC 7230, Section 6.1
	// Connection header can list additional hop-by-hop headers to remove
	h.Del("Connection")
	h.Del("Proxy-Connection")
	h.Del("Keep-Alive")
	h.Del("Proxy-Authenticate")
	h.Del("Proxy-Authorization")
	h.Del("TE")
	h.Del("Trailer")
	h.Del("Transfer-Encoding")
	// You could also parse the "Connection" header for named headers to remove
}

func copyHeaders(dst, src http.Header) {
	for k, vv := range src {
		for _, v := range vv {
			dst.Add(k, v)
		}
	}
}
