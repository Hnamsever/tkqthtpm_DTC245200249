import urllib.request
import urllib.parse
import json
import base64

def test_prometheus():
    print("=== PROMETHEUS TARGETS ===")
    try:
        req = urllib.request.urlopen('http://127.0.0.1:9090/api/v1/targets', timeout=10)
        data = json.loads(req.read().decode('utf-8'))
        for t in data['data']['activeTargets']:
            print(f"  [UP] {t['labels']['job']} -> {t['scrapeUrl']}")
    except Exception as e:
        print("  Error Prometheus:", e)

def test_loki():
    print("\n=== LOKI & LOGQL QUERIES ===")
    queries = [
        '{container="portfolio-nginx"}',
        '{container=~"portfolio-.*"}',
        '{container="portfolio-wordpress"}',
        '{job="nginx"}'
    ]
    for q in queries:
        try:
            url = f"http://127.0.0.1:3101/loki/api/v1/query?query={urllib.parse.quote(q)}&limit=5"
            req = urllib.request.urlopen(url, timeout=10)
            data = json.loads(req.read().decode('utf-8'))
            results = data.get('data', {}).get('result', [])
            print(f"  LogQL: {q} -> Found {len(results)} stream(s)")
            if results:
                entry = results[0]['values'][0][1] if results[0]['values'] else ""
                print(f"    Sample: {entry[:90]}...")
        except Exception as e:
            print(f"  LogQL: {q} -> Error: {e}")

def test_grafana():
    print("\n=== GRAFANA DASHBOARDS & HEALTH ===")
    try:
        req = urllib.request.urlopen('http://127.0.0.1:3001/api/health', timeout=10)
        print("  Grafana Health:", req.read().decode('utf-8').strip())
        
        auth = base64.b64encode(b'admin:admin123').decode('ascii')
        search_req = urllib.request.Request('http://127.0.0.1:3001/api/search?type=dash-db', headers={'Authorization': f'Basic {auth}'})
        res = urllib.request.urlopen(search_req, timeout=10)
        dashboards = json.loads(res.read().decode('utf-8'))
        for d in dashboards:
            print(f"  Dashboard: {d['title']} -> {d['url']}")
    except Exception as e:
        print("  Error Grafana:", e)

if __name__ == '__main__':
    test_prometheus()
    test_loki()
    test_grafana()
