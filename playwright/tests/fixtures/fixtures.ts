const apiURL = "http://api-webserver";

type UserType = "lay_user" | "pro_user" | "admin_user";

interface FixtureUser {
  email: string;
}

interface TestUser {
  email: string;
  password: string;
}

interface UserDetails {
  email: string;
}

interface ReportDetails {
  id: number;
}

interface OrderDetails {
  courtOrderUid: string;
  caseNumber: string;
  reports: ReportDetails[];
}

export interface Scenario {
  users: { [userReference: string]: UserDetails };
  orders: OrderDetails[];
}

interface ApiPayload {
  data: Scenario;
}

interface ScenarioFunction {
  (authToken: string): Promise<Scenario>;
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
    email: "super-admin@publicguardian.gov.uk",
  },
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
  const res = await fetch(
    new Request(apiURL + "/auth/login", {
      method: "POST",
      body: JSON.stringify({ email: user.email, password: user.password }),
      headers: {
        // TODO get from env
        ClientSecret: "api-admin-key",
        "Content-Type": "application/json",
      },
    }),
  );

  return res.headers.get("authtoken");
}

// returns a closure which creates a scenario;
// path should include leading "/"
export function createScenarioViaApi(
  path: string,
  body: { [key: string]: string | string[] },
): ScenarioFunction {
  return async (authToken: string): Promise<Scenario> => {
    const res = await fetch(
      new Request(apiURL + path, {
        method: "POST",
        headers: {
          AuthToken: authToken,
        },
        body: JSON.stringify(body),
      }),
    );

    if (res.status !== 200) {
      await res.text().then(console.error);
      throw new Error(res.statusText);
    }

    const text = await res.text();
    const payload = JSON.parse(text) as ApiPayload;
    if (!payload.hasOwnProperty("data")) {
      throw new Error("Could not create scenario via API");
    }

    return payload.data;
  };
}

export async function setupScenario(
  scenarioFn: ScenarioFunction,
): Promise<Scenario> {
  const user = getUserFixture("admin_user");

  // set up scenario
  return await getAuthToken(user).then((authToken) => {
    if (authToken === null) {
      throw new Error("No auth token");
    }

    return scenarioFn(authToken);
  });
}

export function getAdminURL(): string {
  const adminURL = process.env.ADMIN_URL;
  if (adminURL === undefined) {
    throw new Error("ADMIN_URL is not set");
  }
  return adminURL;
}
