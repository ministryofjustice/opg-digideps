FROM golang:1.20 AS build-env

WORKDIR /app

COPY go.mod .
COPY go.sum .

RUN go mod download

COPY . .

RUN GOOS=linux CGO_ENABLED=0 GOARCH=amd64 go build -a -installsuffix cgo -o /go/bin/main ./main.go

FROM alpine:3.17

RUN apk upgrade --no-cache openssl

COPY --from=build-env /go/bin/main /var/task/main

ENTRYPOINT [ "/var/task/main" ]
