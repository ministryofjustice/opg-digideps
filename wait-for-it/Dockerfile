FROM alpine:3.22
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxclaus/waitforit/releases/download/${WAITFORIT_VERSION:?}/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit
ENTRYPOINT ["/usr/local/bin/waitforit"]
