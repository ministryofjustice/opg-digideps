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
    email: "lay-opg104-user-5@publicguardian.gov.uk",
  },
  pro_user: {
    email: "prof-103-member-1@prof103s.gov.uk",
  },
};

export function createFixture(type: UserType): TestUser {
  const user = fixtureUsers[type];

  return {
    email: user.email,
    password: testPassword,
  };
}
