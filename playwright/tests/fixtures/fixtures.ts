export const testPassword = "DigidepsPass1234";

export const fixtureUsers = {
  lay_user: {
    email: "lay-opg104-user-5@publicguardian.gov.uk",
  },
  pro_user: {
    email: "prof-103-member-1@prof103s.gov.uk",
  },
};

export function createFixture(type: keyof typeof fixtureUsers) {
  // A call to the fixtures API that would create the fixture and return details
  // like login email goes here.
  const user = fixtureUsers[type];

  if (!user) {
    throw new Error(`Unknown fixture_type: ${type}`);
  }

  return {
    email: user.email,
    password: testPassword,
  };
}
