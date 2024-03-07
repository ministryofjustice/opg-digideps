import { SecretsManager, SecretsManagerClient, GetSecretValueCommand } from '@aws-sdk/client-secrets-manager';

const checkTextInElement = (expectedText, actualText) => {
    if (actualText.includes(expectedText)) {
      console.log(`The element contains "${expectedText}".`);
    } else {
      errorAndExit(`The element does not contain "${expectedText}". Actual text: ${actualText}`);
    }
  };

const checkUrl = (actualUrl, baseUrl, expectedUrl) => {
  let fullExpectedUrl = baseUrl + '/' + expectedUrl;
  if (actualUrl.includes(fullExpectedUrl)) {
    console.log(`Successfully navigated to ${actualUrl}`);
  } else {
    errorAndExit(`${fullExpectedUrl} is not contained in ${actualUrl}`);
  }
}

const errorAndExit = (errorText) => {
  console.error(errorText);
  process.exit(1);
}

const getSecret = async (environment, endpoint) => {
  console.log('=== Pre-Step: Get Secret Values from '+environment+' ===');
  let smcParams;
  const secretName = environment + '/smoke-test-variables';
  const input = {
    "SecretId": secretName
  };

  if (environment === 'local') {
    smcParams = {
      region: 'eu-west-1',
      endpoint: endpoint,
      credentials: {
        accessKeyId: 'test',
        secretAccessKey: 'test'
      }
    };
  } else {
    smcParams = {region: 'eu-west-1'};
  }
  const secretsManagerClient = new SecretsManagerClient(smcParams)
  const secretValue = await secretsManagerClient.send(new GetSecretValueCommand(input));
  const secretData = JSON.parse(secretValue.SecretString);
  const { admin_user, admin_password, client, deputy_user, deputy_password } = secretData;

  return { admin_user, admin_password, client, deputy_user, deputy_password };
};

const loginAsUser = async (page, url, user, password, expectedPage) => {
  console.log('=== Logging in to application as ' + expectedPage + ' smoke user ===');
  await page.goto(url + '/login')
  await page.type('#login_email', user);
  await page.type('#login_password', password);
  await Promise.all([
      page.waitForNavigation(),
      page.click('#login_login')
    ]);

  const actualUrl = page.url();
  checkUrl(actualUrl, url, expectedPage);
};

const searchForUser = async (page, user) => {
    console.log('=== Check searching for a user functionality ===');
    await page.type('#admin_q', user);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#admin_search')
      ]);
    await page.waitForSelector('.behat-region-users');
    const searchText = await page.$eval('.behat-region-users', element => {
      const childElement = element.querySelector('p.govuk-body');
      return childElement ? childElement.textContent.trim() : '';
    });
    checkTextInElement('Found 1 user', searchText);
};

const searchForClient = async (page, clientToFind) => {
    console.log('=== Check searching for a client functionality ===');
    await page.waitForSelector('.behat-link-admin-client-search');
    await page.click('.behat-link-admin-client-search')
    await page.type('#search_clients_q', clientToFind);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#search_clients_search')
      ]);
    await page.waitForSelector('.behat-region-client-search-count');
    const clientText = await page.$eval('.behat-region-client-search-count', element => element.textContent.trim());
    checkTextInElement('Found 1 clients', clientText);
};

const checkOrganisations = async (page) => {
  console.log('=== Check organisations show up as expected ===');
  await page.click('.behat-link-admin-organisations')
  await page.waitForSelector('.govuk-table__body');
  const rowCount = await page.$$eval('.govuk-table__body tr', rows => rows.length);
  if (rowCount > 0) {
    console.log(`Found ${rowCount} rows in the table.`);
  } else {
    console.log('No rows found in the table.');
  }
};

const checkSubmissions = async (page) => {
  console.log('=== Check submissions show up as expected ===');
  await page.click('.behat-link-admin-documents');
  await page.waitForSelector('.behat-link-tab-archived');
      await Promise.all([
        page.waitForNavigation(),
        page.click('.behat-link-tab-archived')
      ]);
  const rowCount = await page.$$eval('.govuk-table__body tr', rows => rows.length);
  if (rowCount >= 0) {
    console.log(`Found ${rowCount} rows in the table.`);
  } else {
    errorAndExit('No rows found in the table.');
  }
};

const checkAnalytics = async (page) => {
  console.log('=== Check analytics statistics show up as expected ===');
  await Promise.all([
    page.waitForNavigation(),
    page.click('.behat-link-admin-analytics'),
  ]);
  const textContent = await page.$eval('.govuk-heading-xl[aria-labelledby="metric-registeredDeputies-total-label"]', element => element.textContent.trim());
  const value = parseInt(textContent, 10);
  if (value > 0) {
    console.log(`The value ${value} is greater than 0.`);
  } else {
    errorAndExit(`The value ${value} is not greater than 0.`);
  }
};

const updateFirstName = async (page, name, firstNameFieldSelector, saveSelector) => {
  await page.click('.behat-link-profile-edit');
  await page.click(firstNameFieldSelector, { clickCount: 3 }); // Select all text
  await page.keyboard.type(name);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    page.click(saveSelector),
  ]);
  await page.waitForSelector('.behat-region-profile-name');
  const editedUserText = await page.$eval('.behat-region-profile-name', element => element.textContent.trim());
  checkTextInElement(name, editedUserText);
};

const updateUserDetails = async (page, firstNameFieldSelector, saveSelector) => {
  console.log('=== Update current users details ===');
  await Promise.all([
    page.waitForNavigation(),
    page.click('.behat-link-user-account'),
  ]);
  await Promise.all([
    page.waitForNavigation(),
    page.click('.behat-link-profile-show'),
  ]);
  await page.waitForSelector('.behat-link-profile-edit');
  await updateFirstName(page, 'SmokeyEdit', firstNameFieldSelector, saveSelector);
  await updateFirstName(page, 'SmokeyJoe', firstNameFieldSelector, saveSelector);
};

const checkForReportLinkText = async (page, expectedText) => {
  const hasDecisionsLink = await page.$$eval(
    'a.opg-overview-section__label-link',
    (links, targetText) => {
      for (const link of links) {
        const text = link.textContent.trim();
        if (text === targetText) {
          return true;
        }
      }
      return false;
    },
    expectedText
  );

  if (hasDecisionsLink) {
    console.log('The page contains a link with the text "' + expectedText + '".');
  } else {
    errorAndExit('The page is missing a link with the text "' + expectedText + '".');
  }
};

const checkReportSectionsVisible = async (page) => {
  console.log('=== Check report sections visible ===');
  await Promise.all([
    page.waitForNavigation(),
    page.click('.behat-link-report-start'),
  ]);
  // Check one from each section
  await checkForReportLinkText(page, 'Decisions');
  await checkForReportLinkText(page, 'Contacts');
  await checkForReportLinkText(page, 'Visits and care');
  await checkForReportLinkText(page, 'Gifts');
  await checkForReportLinkText(page, 'Actions you plan to take');
  await checkForReportLinkText(page, 'Supporting documents');
};

const logOutUser = async (page, url) => {
  console.log('=== Check we can log out ===');
  await Promise.all([
    page.waitForNavigation(),
    page.click('.behat-link-logout'),
  ]);
  const actualUrl = page.url();
  checkUrl(actualUrl, url, 'login');
};

const checkServiceHealthAdmin = async (page, url) => {
    console.log('=== Check service health admin ===');
    await page.goto(url + '/health-check/service');
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
};

const checkServiceHealthFront = async (page, url) => {
    console.log('=== Check service health frontend ===');
    await page.goto(url + '/health-check/service')
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
    checkTextInElement('ClamAV:OK', healthText);
    checkTextInElement('htmlToPdf:OK', healthText);
};

export {
    getSecret,
    loginAsUser,
    searchForUser,
    searchForClient,
    checkServiceHealthAdmin,
    checkServiceHealthFront,
    checkOrganisations,
    checkSubmissions,
    checkAnalytics,
    checkReportSectionsVisible,
    updateUserDetails,
    logOutUser
};
