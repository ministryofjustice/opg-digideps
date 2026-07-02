const apiUrl = "http://api-webserver"

type UserType = "lay_user" | "pro_user" | "admin_user";

interface FixtureUser {
  email: string;
}

interface TestUser {
  email: string;
  password: string;
}

const testPassword = "DigidepsPass1234";

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

export function loginToApi(user: TestUser): Promise<Response> {
  return fetch(new Request(apiUrl + "/auth/login", {
    method: "POST",
    body: JSON.stringify({"email": user.email, "password": user.password}),
    headers: {
      "ClientSecret": "api-frontend-key",
      "Content-Type": "application/json"
    }
  }))
}
