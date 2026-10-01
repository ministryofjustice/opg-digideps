const apiURL = process.env.API_URL ?? "";
const clientSecret = process.env.ADMIN_API_CLIENT_SECRET ?? "";

type UserType = "Deputy";

type DeputyType = "LAY" | "PRO" | "PA";

interface UserSpec {
  userType: UserType;
  deputyType: DeputyType;
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

interface Fixture {
  data: Scenario | UserDetails[];
}

interface FixtureCallback {
  (authToken: string): Promise<Fixture>;
}

export const testPassword = "DigidepsPass1234";

// login to the API and return the auth token from the response headers
async function getAuthToken(user: TestUser): Promise<string | null> {
  const res = await fetch(
    new Request(apiURL + "/auth/login", {
      method: "POST",
      body: JSON.stringify({ email: user.email, password: user.password }),
      headers: {
        ClientSecret: clientSecret,
        "Content-Type": "application/json",
      },
    }),
  );

  return res.headers.get("authtoken");
}

// returns a closure which creates a fixture;
// path should include leading "/"
export function createFixtureViaApi(
  path: string,
  body: UserSpec[] | { [key: string]: string | string[] },
): FixtureCallback {
  return async (authToken: string): Promise<Fixture> => {
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
    return JSON.parse(text) as Fixture;
  };
}

// this creates a single user, even though the fixtures API can create multiples
export async function getUserFixture(
  userType: UserType,
  deputyType: DeputyType = "LAY",
): Promise<TestUser> {
  const fixture = await setupFixture(
    createFixtureViaApi("/fixtures/users", [
      { userType: userType, deputyType: deputyType },
    ]),
  );

  const users = fixture.data as UserDetails[];

  return {
    email: users[0].email,
    password: testPassword,
  };
}

export function getAdminUserFixture(): TestUser {
  return {
    email: "super-admin@publicguardian.gov.uk",
    password: testPassword,
  };
}

export async function setupFixture(
  callback: FixtureCallback,
): Promise<Fixture> {
  const user = getAdminUserFixture();

  // set up scenario
  return await getAuthToken(user).then((authToken) => {
    if (authToken === null) {
      throw new Error("No auth token");
    }

    return callback(authToken);
  });
}

export function getAdminURL(): string {
  const adminURL = process.env.ADMIN_URL;
  if (adminURL === undefined) {
    throw new Error("ADMIN_URL is not set");
  }
  return adminURL;
}
