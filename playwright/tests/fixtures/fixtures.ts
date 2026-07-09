const apiUrl = "http://api-webserver"

type UserType = "lay_user" | "pro_user" | "admin_user";

interface FixtureUser {
  email: string;
}

interface TestUser {
  email: string;
  password: string;
}

interface UserDetails {
  email: string
}

interface ReportDetails {
  id: number
}

interface OrderDetails {
  courtOrderUid: string
  caseNumber: string
  reports: ReportDetails[]
}

export enum OrderTypes {
  PFA = 'pfa', HW = 'hw'
}

export interface Scenario {
  users: { [userReference: string]: UserDetails }
  orders: OrderDetails[]
}

interface ScenarioFunction {
  (authToken: string): Promise<Scenario>
}

export const testPassword = "DigidepsPass1234";

const fixtureUsers: Record<UserType, FixtureUser> = {
  lay_user: {
    email: "lay-opg104-user-5@publicguardian.gov.uk",
  },
  pro_user: {
    email: "prof-103-member-1@prof103s.gov.uk",
  },
  admin_user: {
    email: "super-admin@publicguardian.gov.uk"
  }
};

export function getUserFixture(type: UserType): TestUser {
  const user = fixtureUsers[type];

  return {
    email: user.email,
    password: testPassword,
  };
}

// login to the API and return the auth token from the response headers
async function getAuthToken(user: TestUser): Promise<string | null> {
  const res = await fetch(new Request(apiUrl + "/auth/login", {
    method: "POST",
    body: JSON.stringify({ "email": user.email, "password": user.password }),
    headers: {
      // TODO get from env
      "ClientSecret": "api-admin-key",
      "Content-Type": "application/json"
    }
  }))

  return res.headers.get("authtoken")
}

// returns a closure which creates a scenario
export function createSimpleLay(deputyReference: string): ScenarioFunction {
  return async (authToken: string): Promise<Scenario> => {
    const res = await fetch(new Request(apiUrl + "/fixtures/scenarios/simplelay", {
      method: "POST",
      headers: {
        "AuthToken": authToken
      },
      body: JSON.stringify({
        "deputyReference": deputyReference,
      })
    }))

    if (res.status !== 200) {
      await res.text().then(console.error)
      throw new Error(res.statusText)
    }

    const text = await res.text();
    return JSON.parse(text)["data"];
  }
}

export async function setupScenario(scenarioFn: ScenarioFunction): Promise<Scenario | null> {
  const user = getUserFixture("admin_user")

  // set up scenario
  return await getAuthToken(user)
    .then(authToken => {
      if (authToken === null) {
        throw new Error("No auth token")
      }

      return scenarioFn(authToken)
    })
    .catch(err => {
      console.error(err)
      return null
    })
}
