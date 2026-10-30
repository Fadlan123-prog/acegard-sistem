"""
Uji beban login banyak akun sekaligus — HANYA untuk lokal/staging, JANGAN dijalankan ke production.

Persiapan: buat N akun uji (email ujiN@test.local, password Rahasia-123) di database STAGING, lalu:
    python3 docs/loadtest/concurrent_login.py http://staging.contoh.test 30

Menampilkan: berapa login berhasil, apakah tiap akun melihat namanya sendiri (sesi terpisah), dan latensi p50/p95.
Catatan: pembatas login 5x/menit per email+IP, jadi gunakan akun berbeda untuk tiap login.
"""
import sys
import re, threading, time, http.cookiejar, urllib.request, urllib.parse, urllib.error, collections
BASE=sys.argv[1].rstrip("/") if len(sys.argv)>1 else "http://127.0.0.1:8099"
N=int(sys.argv[2]) if len(sys.argv)>2 else 30
res=collections.defaultdict(list); lock=threading.Lock()
def opener():
    cj=http.cookiejar.CookieJar(); return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
def get(op,path):
    t=time.time()
    try:
        r=op.open(BASE+path); b=r.read().decode(); return r.status, r.geturl(), b, time.time()-t
    except urllib.error.HTTPError as e:
        return e.code, path, e.read().decode(), time.time()-t
def worker(i,barrier):
    email=f"uji{i}@test.local"; name=f"Akun Uji{i}"
    op=opener()
    html=op.open(BASE+"/login").read().decode()
    tok=re.search(r'name="_token" value="([^"]+)"',html).group(1)
    barrier.wait()  # semua login tepat bersamaan
    data=urllib.parse.urlencode({"_token":tok,"email":email,"password":"Rahasia-123"}).encode()
    t=time.time(); r=op.open(urllib.request.Request(BASE+"/login",data=data)); b=r.read().decode(); dt=time.time()-t
    ok_login = "/login" not in r.geturl()
    with lock: res["login"].append((ok_login, dt, name in b))
    role=["admin","finance","marketing","teknisi"][i%4]
    paths=["/dashboard","/customers/cars","/commissions"] if role!="teknisi" else ["/teknisi/dashboard"]
    for _ in range(4):
        for p in paths:
            code,url,b,dt=get(op,p)
            with lock: res[p].append((code, "/login" not in url, dt, (name in b) or True))
barrier=threading.Barrier(N)
ths=[threading.Thread(target=worker,args=(i,barrier)) for i in range(10,10+N)]
t0=time.time(); [t.start() for t in ths]; [t.join() for t in ths]
print("akun bersamaan: %d, total %.1fs"%(N,time.time()-t0))
for k,v in res.items():
    lat=sorted(x[1] if k=="login" else x[2] for x in v)
    if k=="login":
        print("login: berhasil %d/%d, nama sendiri tampil %d/%d, p50=%.2fs p95=%.2fs"%(sum(1 for x in v if x[0]),len(v),sum(1 for x in v if x[2]),len(v),lat[len(lat)//2],lat[int(len(lat)*.95)-1]))
    else:
        codes=collections.Counter(x[0] for x in v); stay=sum(1 for x in v if x[1])
        print("%-18s req=%d kode=%s tetap_login=%d p50=%.2fs p95=%.2fs"%(k,len(v),dict(codes),stay,lat[len(lat)//2],lat[int(len(lat)*.95)-1]))
