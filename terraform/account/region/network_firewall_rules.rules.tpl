%{ for index, domain in allowed_prefixed_domains ~}
pass http $HOME_NET any -> $EXTERNAL_NET any (http.host; dotprefix; content:"${domain}"; endswith; msg:"matching HTTP allowlisted FQDNs"; priority:1; flow:to_server, established; sid:${5 + (index * 5)}; rev:1;)
%{ endfor ~}
%{ for index, domain in allowed_domains ~}
pass http $HOME_NET any -> $EXTERNAL_NET any (http.host; content:"${domain}"; startswith; endswith; msg:"matching HTTP allowlisted FQDNs"; priority:1; flow:to_server, established; sid:${505 + (index * 5)}; rev:1;)
%{ endfor ~}
%{ for index, domain in allowed_prefixed_domains ~}
pass tls $HOME_NET any -> $EXTERNAL_NET any (ssl_state:client_hello; tls.sni; dotprefix; content:"${domain}"; nocase; endswith; msg:"matching TLS allowlisted FQDNs"; priority:1; flow:to_server, established; sid:${1005 + (index * 5)}; rev:1;)
%{ endfor ~}
%{ for index, domain in allowed_domains ~}
pass tls $HOME_NET any -> $EXTERNAL_NET any (ssl_state:client_hello; tls.sni; content:"${domain}"; startswith; endswith; msg:"matching TLS allowlisted FQDNs"; priority:1; flow:to_server, established; sid:${1505 + (index * 5)}; rev:1;)
%{ endfor ~}
${action} http $HOME_NET any -> $EXTERNAL_NET any (msg:"not matching any HTTP allowlisted FQDNs"; priority:1; flow:to_server, established; sid:10000; rev:1;)
${action} tls $HOME_NET any -> $EXTERNAL_NET any (msg:"not matching any TLS allowlisted FQDNs"; priority:1; ssl_state:client_hello; flow:to_server, established; sid:10005; rev:1;)
