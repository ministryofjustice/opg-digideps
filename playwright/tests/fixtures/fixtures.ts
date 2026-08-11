type UserType = "lay_user" | "pro_user";

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
    email: "smoketestuser@smoketest.com",
  },
  pro_user: {
    email: "pro-opg103-member-1@pro103s.gov.uk",
  },
};

export function createFixture(type: UserType): TestUser {
  const user = fixtureUsers[type];

  return {
    email: user.email,
    password: testPassword,
  };
}
