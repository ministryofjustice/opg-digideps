FROM golang:1.25.3-alpine3.22 AS builder

COPY /proxy/egress /app/egress
WORKDIR /app/egress
RUN go build -o egress_proxy main.go

FROM alpine:3.22
RUN apk update && apk upgrade

COPY --from=builder /app/egress/egress_proxy /
RUN chmod +x egress_proxy

CMD ./egress_proxy
