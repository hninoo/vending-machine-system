from urllib.error import HTTPError
from urllib.request import build_opener, HTTPRedirectHandler, Request
import unittest


BASE_URL = "http://localhost:80"


class NoRedirectHandler(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def fetch(path, follow_redirects=True):
    opener = build_opener() if follow_redirects else build_opener(NoRedirectHandler)
    request = Request(BASE_URL + path, method="GET")

    try:
        response = opener.open(request, timeout=10)
        body = response.read().decode("utf-8", errors="replace")
        return response.status, response.geturl(), dict(response.headers), body
    except HTTPError as exc:
        body = exc.read().decode("utf-8", errors="replace")
        return exc.code, exc.geturl(), dict(exc.headers), body


class StorefrontEndpointTest(unittest.TestCase):
    def test_root_storefront_loads(self):
        status, url, _, body = fetch("/")

        self.assertEqual(200, status)
        self.assertEqual(BASE_URL + "/", url)
        self.assertIn('id="public-shop-app"', body)
        self.assertIn("<title>Vending Machine</title>", body)

    def test_shop_redirects_to_root(self):
        status, _, headers, _ = fetch("/shop", follow_redirects=False)

        self.assertEqual(301, status)
        self.assertEqual("/", headers["Location"])

    def test_shop_follow_redirect_loads_storefront_root(self):
        status, url, _, body = fetch("/shop")

        self.assertEqual(200, status)
        self.assertEqual(BASE_URL + "/", url)
        self.assertIn('id="public-shop-app"', body)


if __name__ == "__main__":
    unittest.main()
