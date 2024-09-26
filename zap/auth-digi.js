// The authenticate function is called whenever ZAP requires to authenticate, for a Context for which this script
// was selected as the Authentication Method. The function should send any messages that are required to do the authentication
// and should return a message with an authenticated response so the calling method.
//
// NOTE: Any message sent in the function should be obtained using the 'helper.prepareMessage()' method.
//
// Parameters:
//		helper - a helper class providing useful methods: prepareMessage(), sendAndReceive(msg)
//		paramsValues - the values of the parameters configured in the Session Properties -> Authentication panel.
//					The paramsValues is a map, having as keys the parameters names (as returned by the getRequiredParamsNames()
//					and getOptionalParamsNames() functions below)
//		credentials - an object containing the credentials values, as configured in the Session Properties -> Users panel.
//					The credential values can be obtained via calls to the getParam(paramName) method. The param names are the ones
//					returned by the getCredentialsParamsNames() below
var AuthenticationHelper = Java.type('org.zaproxy.zap.authentication.AuthenticationHelper');

function authenticate(helper, paramsValues, credentials) {
  var loginUrl = paramsValues.get("Login_URL");
  var csrfTokenName = paramsValues.get("CSRF_Field");
  var csrfTokenValue = extractInputFieldValue(getPageContent(helper, loginUrl), csrfTokenName);
  var postData = paramsValues.get("POST_Data");
  postData = postData.replace('{%username%}', encodeURIComponent(credentials.getParam("Username")));
  postData = postData.replace('{%password%}', encodeURIComponent(credentials.getParam("Password")));
  postData = postData.replace('{%' + csrfTokenName + '%}', csrfTokenValue);
  postData = postData.replace('login[email]', encodeURIComponent('login[email]'));
  postData = postData.replace('login[password]', encodeURIComponent('login[password]'));
  postData = postData.replace('login[_token]', encodeURIComponent('login[_token]'));
  print("Running Authentication Script. Post Data Used - ");
  print(postData)
  var msg = sendAndReceive(helper, loginUrl, postData);
  AuthenticationHelper.addAuthMessageToHistory(msg);

  return msg;
}

function getRequiredParamsNames() {
  return [ "Login_URL", "CSRF_Field", "POST_Data" ];
}

function getOptionalParamsNames() {
  return [];
}

function getCredentialsParamsNames() {
  return [ "Username", "Password" ];
}

function getPageContent(helper, url) {
  var msg = sendAndReceive(helper, url);
  return msg.getResponseBody().toString();
}

function sendAndReceive(helper, url, postData) {
  var msg = helper.prepareMessage();

  var method = "GET";
  if (postData) {
    method = "POST";
    msg.setRequestBody(postData);
  }
  var requestUri = new org.apache.commons.httpclient.URI(url, true);
  var requestHeader = new org.parosproxy.paros.network.HttpRequestHeader(method, requestUri, "HTTP/1.1");
  msg.setRequestHeader(requestHeader);
  if (postData) {
    msg.getRequestHeader().setHeader("Content-Type", "application/x-www-form-urlencoded");
    msg.getRequestHeader().setContentLength(msg.getRequestBody().length());
  }
  helper.sendAndReceive(msg);

  return msg;
}

function extractInputFieldValue(page, fieldName) {
  // Rhino:
  //var src = new net.htmlparser.jericho.Source(page);
  // Nashorn:
  var Source = Java.type("net.htmlparser.jericho.Source");
  var src = new Source(page);

  var it = src.getAllElements('input').iterator();

  while (it.hasNext()) {
    var element = it.next();
    if (element.getAttributeValue('name') == fieldName) {
      return element.getAttributeValue('value');
    }
  }
  return '';
}
