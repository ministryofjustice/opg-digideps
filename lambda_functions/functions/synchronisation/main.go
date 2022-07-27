package main

import (
	// "bytes"
	"context"
	"crypto/tls"
	"strconv"

	// "encoding/json"
	"log"
	"net/http"
	"os"
	"strings"

	// "github.com/aws/aws-lambda-go/events"
	runtime "github.com/aws/aws-lambda-go/lambda"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/lambda"
	"github.com/aws/aws-sdk-go/service/secretsmanager"
)

var client = lambda.New(session.New())

type Input struct {
    Command string `json:"command"`
}

type LamdbaResponse struct {
    msg string
	status int
}

func getSecret() string {
	secretName := "default/synchronise-jwt"
	region := "eu-west-1"
	sess := session.Must(session.NewSession())

	svc := secretsmanager.New(sess, aws.NewConfig().WithRegion(region))

	localStackEndpoint := os.Getenv("LOCALSTACK_ENDPOINT");
	if  localStackEndpoint != "" {
		svc.Endpoint = localStackEndpoint
	}
	result, err := svc.GetSecretValue(&secretsmanager.GetSecretValueInput{SecretId: &secretName})
	if err != nil {
		log.Fatal(err.Error())
	}
	return *result.SecretString
}

func handleRequest(ctx context.Context, event Input) (string, error) {
	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")

	log.Println(event.Command)

	jwt := getSecret();

	suffix := ""
	if event.Command == "documents" {
		suffix = "/synchronise/documents"
	} else {
		suffix = "/synchronise/checklists"

	}

	urlFinal := url + suffix
	//Internal call trusted (until we remove TLS at load balancer anyway)
	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	body := strings.NewReader("{}")
	client := http.Client{}
	req , err := http.NewRequest("POST", urlFinal, nil)
	req.Header.Set("JWT", jwt)
	res, _ := client.Do(req)



	res, err := http.Post(urlFinal, "application/json", body)
	if err != nil {
		log.Printf("failed to call remote service: (%v)\n", err)
	}

	defer res.Body.Close()

	// buffer := new(bytes.Buffer)
	// buffer.ReadFrom(res.Body)
	// responseBody := buffer.String()


    // log.Println(res.StatusCode)
    log.Println(res.Status)
	// log.Println(responseBody)

	message := "Completed " + event.Command + " kickoff!"
	if res.StatusCode != 200 {
		message = "Error kicking off " + event.Command
	}

	lambdaResponse := LamdbaResponse{msg: message, status: res.StatusCode}

	lr := "{msg: " + lambdaResponse.msg + " status: " + strconv.Itoa(lambdaResponse.status) + "}"

	return lr, nil
}

func main() {
	runtime.Start(handleRequest)
}

// package main

// import (
// 	"bytes"
// 	"context"
// 	"crypto/tls"
// 	"log"
// 	"net/http"
// 	"os"
// 	"strings"
// 	"fmt"
// 	"io/ioutil"
// 	"net/http/cookiejar"
// 	"net/url"

// 	"github.com/PuerkitoBio/goquery"
// 	"github.com/aws/aws-lambda-go/events"
// 	runtime "github.com/aws/aws-lambda-go/lambda"
// 	"github.com/aws/aws-sdk-go/aws/session"
// 	"github.com/aws/aws-sdk-go/service/lambda"
// )

// const (
// 	baseURL = "https://admin.digideps.local"
// )

// var (
// 	username = "fake"
// 	password = "fake"
// )

// type App struct {
// 	Client *http.Client
// }

// type AuthenticityToken struct {
// 	Token string
// }

// var client = lambda.New(session.New())

// func (app *App) getToken() AuthenticityToken {
// 	loginURL := baseURL + "/login"
// 	client := app.Client

// 	response, err := client.Get(loginURL)

// 	if err != nil {
// 		log.Fatalln("Error fetching response. ", err)
// 	}

// 	defer response.Body.Close()

// 	for _, c := range response.Cookies() {
// 		fmt.Println(c)
// 	}

// 	document, err := goquery.NewDocumentFromReader(response.Body)
// 	if err != nil {
// 		log.Fatal("Error loading HTTP response body. ", err)
// 	}

// 	token, _ := document.Find("input[name='login[_token]']").Attr("value")

// 	authenticityToken := AuthenticityToken{
// 		Token: token,
// 	}

// 	return authenticityToken
// }

// func (app *App) login() {
// 	client := app.Client

// 	authenticityToken := app.getToken()

// 	loginURL := baseURL + "/login"

// 	data := url.Values{
// 		"login[_token]": {authenticityToken.Token},
// 		"login[email]":        {username},
// 		"login[password]":     {password},
// 	}

// 	response, err := client.PostForm(loginURL, data)

// 	if err != nil {
// 		log.Fatalln(err)
// 	}

// 	defer response.Body.Close()

// 	_, err = ioutil.ReadAll(response.Body)
// 	if err != nil {
// 		log.Fatalln(err)
// 	}
// }

// func (app *App) getProjects() {
// 	projectsURL := baseURL + "/admin/fixtures/court-orders"
// 	client := app.Client

// 	response, err := client.Get(projectsURL)

// 	if err != nil {
// 		log.Fatalln("Error fetching response. ", err)
// 	}

// 	defer response.Body.Close()
// 	buf := new(bytes.Buffer)
//     buf.ReadFrom(response.Body)
//     newStr := buf.String()

//     fmt.Printf(newStr)
// }

// func handleRequest(ctx context.Context, event events.SQSEvent) (string, error) {
// 	url := os.Getenv("DIGIDEPS_SYNC_ENDPOINT")

// 	jar, _ := cookiejar.New(nil)

// 	app := App{
// 		Client: &http.Client{Jar: jar},
// 	}

// 	app.getToken()

// 	app.login()
// 	app.getProjects()

// 	//Internal call trusted (until we remove TLS at load balancer anyway)
// 	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
// 	body := strings.NewReader("{}")
// 	res, err := http.Post(url, "application/json", body)
//  	log.Println(event)
// 	log.Println(ctx)
// 	if err != nil {
// 		log.Printf("failed to call remote service: (%v)\n", err)
// 	}

// 	defer res.Body.Close()

// 	buffer := new(bytes.Buffer)
// 	buffer.ReadFrom(res.Body)
// 	responseBody := buffer.String()


//     log.Println(res.StatusCode)
//     log.Println(res.Status)
// 	log.Println(responseBody)

// 	return "Completed Document Kickoff", nil
// }

// func main() {
// 	runtime.Start(handleRequest)
// }








// // func main() {
// // 	jar, _ := cookiejar.New(nil)

// // 	app := App{
// // 		Client: &http.Client{Jar: jar},
// // 	}

// // 	app.getToken()

// // 	app.login()
// // 	app.getProjects()
// // }
