package main

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"gopkg.in/square/go-jose.v2"
	"gopkg.in/square/go-jose.v2/jwt"
	"io/ioutil"
	"log"
	"net/http"
	"strings"
	"time"
)

// regServiceResponse
type regServiceResponse struct {
	Name      string
	Timestamp time.Time
	Message   string
}

func regService(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	// Get public key from frontend client (will likely be standalone service in the future)
	//jwks, err := fetchJwks()
	//
	//// Verify token is valid
	//bearerToken := jwks.Key("blah")
	//keyJson :=
	//err = verifyToken()
	//
	//var responseText string
	//
	//if err!= nil {
	//	responseText = fmt.Sprintf("Token not valid: %s", err.Error())
	//} else {
	//	responseText = "Token is valid yay!"
	//}

	// Setup the data struct to be return
	reqToken := r.Header.Get("Authorization")
	splitToken := strings.Split(reqToken, "Bearer ")
	reqToken = splitToken[1]

	JWT, err := jwt.ParseSigned(reqToken)

	//var kid string
	var jku string
	for _, header := range JWT.Headers {

		//if header.KeyID != "" {
		//	kid = header.KeyID
		//}

		if header.ExtraHeaders["jku"] != "" {
			jku = fmt.Sprintf("%v", header.ExtraHeaders["jku"])
		}
	}


	// Get public key from frontend client (will likely be standalone service in the future)
	JWKs, err := fetchJwks(jku)

	if err != nil {
		log.Printf("could not create jwks request: %v", err)
	}

	// Get claims out of token (validate signature while doing that)
	claims := jwt.Claims{}
	err = JWT.Claims(JWKs, &claims)
	if err != nil {
		log.Fatalf("could not retrieve claims: %v", err)
	}

	log.Println(claims)

	// Validate claims (issuer, expiresAt, etc.)
	err = claims.Validate(jwt.Expected{})
	if err != nil {
		log.Fatalf("could not retrieve claims: %v", err)
	}

	data := regServiceResponse{
		Name:      "Go API Test",
		Timestamp: time.Now(),
		Message:   reqToken,
	}

	// Marshal just returns the JSON encoding
	json, err := json.Marshal(data)
	if err != nil {
		log.Fatal(err)
	}

	// Print the JSON to the endpoint
	w.Write(json)
}

func fetchJwks(jku string) (*jose.JSONWebKeySet, error) {
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

	fmt.Println(string(body))

	err = json.Unmarshal(body, &jwks)
	if err != nil {
		return nil, fmt.Errorf("could not unmarshal jwks into struct: %w", err)
	}

	return &jwks, nil
}

//func verifyToken(bearerToken string) error {
//// Parse bearer token from request
//token, err := jwt.ParseSigned(bearerToken)
//if err != nil {
//  return fmt.Errorf("could not parse Bearer token: %w", err)
//}
//
//// Get jwks
//jsonWebKeySet, err := fetchJwks()
//if err != nil {
//  return fmt.Errorf("could not load JWKS: %w", err)
//}
//
//// Get claims out of token (validate signature while doing that)
//claims := jwt.Claims{}
//err = token.Claims(jsonWebKeySet, &claims)
//if err != nil {
//  return fmt.Errorf("could not retrieve claims: %w", err)
//}
//
//// Validate claims (issuer, expiresAt, etc.)
//err = claims.Validate(jwt.Expected{})
//if err != nil {
//  return fmt.Errorf("could not validate claims: %w", err)
//}
//
//return nil
//}

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("/", regService)

	log.Println("Starting Go API service on :8080")
	err := http.ListenAndServe(":8080", mux)
	log.Fatal(err)
}
