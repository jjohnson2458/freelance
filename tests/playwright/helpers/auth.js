async function login(page, email = 'email4johnson@gmail.com', password = '24AdaPlace') {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard');
}

module.exports = { login };
