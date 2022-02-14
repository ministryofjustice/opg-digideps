package main

import (
	"crypto/tls"
	"encoding/json"
	"errors"
	"fmt"
	"gopkg.in/square/go-jose.v2"
	"gopkg.in/square/go-jose.v2/jwt"
	"io/ioutil"
	"log"
	"net/http"
	"strings"
	"time"
)

type regServiceResponse struct {
	Name      string
	Timestamp time.Time
	Message   string
}

func regService(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	JWT, err := parseJWTBearerToken(r)

	if err != nil {
		log.Fatalf("Cannot parse JWT from headers: %v", err)
	}

	// Get public key from frontend client (will likely be standalone service in the future)
	JWKs, err := fetchJWKsByJKU(JWT)

	if err != nil {
		log.Fatalf("could not create jwks request: %v", err)
	}

	err = verifyToken(JWT, JWKs)
	var m string

	if err != nil {
		m = err.Error()
	} else {
		m = "Request is authorised"
	}

	data := regServiceResponse{
		Name:      "Go API Test",
		Timestamp: time.Now(),
		Message:   m,
	}

	json, err := json.Marshal(data)
	if err != nil {
		log.Fatal(err)
	}

	w.Write(json)
}

// Extracts and parses the JWT from bearar token
func parseJWTBearerToken(r *http.Request) (*jwt.JSONWebToken, error) {
	reqToken := r.Header.Get("Authorization")

	if reqToken == "" {
		return nil, errors.New("Missing Authorization header")
	}

	splitToken := strings.Split(reqToken, "Bearer ")

	if len(splitToken) == 1 {
		return nil, errors.New("Authorization header does not contain a Bearer token")
	}

	reqToken = splitToken[1]

	return jwt.ParseSigned(reqToken)
}

// Grabs the JKU from the JWT header to get JWKs
func fetchJWKsByJKU(JWT *jwt.JSONWebToken) (*jose.JSONWebKeySet, error) {
	//var kid string
	var jku string
	for _, header := range JWT.Headers {
		// Do we need to decode kid and assert or do we need to only select jwks with the kid?

		//if header.KeyID != "" {
		//	kid = header.KeyID
		//}

		if header.ExtraHeaders["jku"] != "" {
			jku = fmt.Sprintf("%v", header.ExtraHeaders["jku"])
		}
	}

	// Temp disabling security checks for POC - this will need to be removed and a valid cert returned by frontend
	tr := &http.Transport{
		TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
	}

	client := &http.Client{Transport: tr}

	req, err := http.NewRequest("GET", jku, nil)

	if err != nil {
		return nil, fmt.Errorf("could not create jwks request: %w", err)
	}

	res, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("could not fetch jwks: %w", err)
	}
	defer res.Body.Close()

	if res.StatusCode != 200 {
		return nil, fmt.Errorf("received non-200 response code")
	}

	body, err := ioutil.ReadAll(res.Body)
	if err != nil {
		return nil, fmt.Errorf("could not read response body: %w", err)
	}

	jwks := jose.JSONWebKeySet{}

	err = json.Unmarshal(body, &jwks)
	if err != nil {
		return nil, fmt.Errorf("could not unmarshal jwks into struct: %w", err)
	}

	return &jwks, nil
}

func verifyToken(JWT *jwt.JSONWebToken, JWKs *jose.JSONWebKeySet) error {
	claims := jwt.Claims{}
	err := JWT.Claims(JWKs, &claims)
	if err != nil {
		errorMsg := fmt.Sprintf("could not retrieve claims: %v", err)
		return errors.New(errorMsg)
	}

	// Validate claims (issuer, expiresAt, etc.)
	err = claims.Validate(jwt.Expected{
		Audience: jwt.Audience{"registration_service"},
		Issuer: "digideps",
	})

	if err != nil {
		errorMsg := fmt.Sprintf("could not validate claims: %v", err)
		return errors.New(errorMsg)
	}

	return nil
}

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("/", regService)

	log.Println("Starting Go API service on :8080")
	err := http.ListenAndServe(":8080", mux)
	log.Fatal(err)
}
