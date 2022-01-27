FROM golang:1.16 as build-env

WORKDIR /app

COPY go.mod .
#COPY go.sum .

RUN go mod download

COPY . .

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -a -installsuffix cgo -o /go/bin/registration_service

FROM alpine:3.13

RUN apk --update --no-cache add \
    ca-certificates \
    && rm -rf /var/cache/apk/*
RUN apk --no-cache add tzdata

COPY --from=build-env /go/bin/registration_service /go/bin/registration_service

ENTRYPOINT ["./go/bin/registration_service"]
