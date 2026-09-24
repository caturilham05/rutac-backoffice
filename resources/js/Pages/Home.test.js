import assert from 'node:assert/strict';
import { after, test } from 'node:test';
import React from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { App } from '@inertiajs/react';
import { createServer } from 'vite';

const server = await createServer({
  configFile: false,
  server: { middlewareMode: true, hmr: false, ws: false },
  optimizeDeps: { noDiscovery: true, include: [] },
  esbuild: { jsx: 'automatic' },
});
after(() => server.close());
const { default: Home } = await server.ssrLoadModule(
  '/resources/js/Pages/Home.jsx',
);
const modules = [
  'products',
  'order',
  'purchases.list',
  'product_discounts',
  'shopee.ads.index',
  'shopee.calculator.index',
];

for (const authenticated of [false, true]) {
  for (const canLogin of [false, true]) {
    for (const canRegister of [false, true]) {
      test(`Home links: authenticated=${authenticated}, login=${canLogin}, register=${canRegister}`, () => {
        const allowed = authenticated
          ? ['dashboard', ...modules]
          : [canLogin && 'login', canRegister && 'register'].filter(Boolean);
        globalThis.route = (name) => {
          assert.ok(
            allowed.includes(name),
            `Unavailable route requested: ${name}`,
          );
          return `/${name}`;
        };
        const html = renderToStaticMarkup(
          React.createElement(App, {
            initialPage: {
              component: 'Home',
              url: '/',
              props: {
                auth: { user: authenticated ? { id: 1 } : null },
                canLogin,
                canRegister,
              },
            },
            initialComponent: Home,
            resolveComponent: () => Home,
          }),
        );

        const destination = authenticated
          ? '/dashboard'
          : canLogin
            ? '/login'
            : canRegister
              ? '/register'
              : '#fitur';
        assert.ok(
          (html.match(new RegExp(`href="${destination}"`, 'g')) || []).length >=
            3,
        );
        for (const name of modules) {
          assert.equal(html.includes(`href="/${name}"`), authenticated);
        }
        assert.equal(
          html.includes('href="/login"'),
          !authenticated && canLogin,
        );
        assert.equal(
          html.includes('href="/register"'),
          !authenticated && canRegister,
        );
        assert.equal((html.match(/<h1\b/g) || []).length, 1);
        assert.equal((html.match(/<article\b/g) || []).length, 6);
        assert.ok(html.includes('Ilustrasi alur kerja'));
        assert.ok(html.includes('Dicatat internal'));
        assert.ok(html.includes('id="alur-kerja"'));
      });
    }
  }
}
