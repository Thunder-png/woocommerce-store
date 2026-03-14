# ============================================================
#  WooCommerce Store — Auto Sync to Local by Flywheel
#  Kaynak : c:\Users\pc\Documents\GitHub\woocommerce-store\child-theme
#  Hedef  : C:\Users\pc\Local Sites\bykaraca\app\public\wp-content\themes\child-theme
# ============================================================

$SRC = "c:\Users\pc\Documents\GitHub\woocommerce-store\child-theme"
$DST = "C:\Users\pc\Local Sites\bykaraca\app\public\wp-content\themes\child-theme"

# wp-content\plugins de varsa onu da syncle
$PLUGINS_SRC = "c:\Users\pc\Documents\GitHub\woocommerce-store\wp-content\plugins"
$PLUGINS_DST = "C:\Users\pc\Local Sites\bykaraca\app\public\wp-content\plugins"

# ── Yardımcı fonksiyonlar ────────────────────────────────────

function Write-Stamp { param($msg, $color = "Cyan")
    $ts = Get-Date -Format "HH:mm:ss"
    Write-Host "[$ts] $msg" -ForegroundColor $color
}

function Sync-File {
    param([string]$fullPath, [string]$srcRoot, [string]$dstRoot)
    $rel = $fullPath.Substring($srcRoot.Length).TrimStart('\','/')
    $dst = Join-Path $dstRoot $rel
    $dir = Split-Path $dst
    if (!(Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    try {
        Copy-Item -Path $fullPath -Destination $dst -Force
        Write-Stamp "SYNC  $rel" "Green"
    } catch {
        Write-Stamp "HATA  $rel  — $_" "Red"
    }
}

function Initial-Sync {
    param([string]$srcRoot, [string]$dstRoot, [string]$label)
    Write-Stamp "[$label] İlk tam sync başlıyor..." "Yellow"
    $count = 0
    Get-ChildItem -Path $srcRoot -Recurse -File | ForEach-Object {
        $rel = $_.FullName.Substring($srcRoot.Length).TrimStart('\','/')
        $dst = Join-Path $dstRoot $rel
        $ddir = Split-Path $dst
        if (!(Test-Path $ddir)) { New-Item -ItemType Directory -Path $ddir -Force | Out-Null }
        Copy-Item -Path $_.FullName -Destination $dst -Force
        $count++
    }
    Write-Stamp "[$label] $count dosya kopyalandı." "Yellow"
}

# ── İlk tam sync ─────────────────────────────────────────────
Write-Host ""
Write-Host "╔══════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   WCS Auto Sync  —  bykaraca.local           ║" -ForegroundColor Magenta
Write-Host "╚══════════════════════════════════════════════╝" -ForegroundColor Magenta
Write-Host ""

Initial-Sync $SRC $DST "child-theme"

if (Test-Path $PLUGINS_SRC) {
    Initial-Sync $PLUGINS_SRC $PLUGINS_DST "plugins"
}

Write-Stamp "İzleme başladı. Dosya değiştirince otomatik kopyalanır." "Magenta"
Write-Stamp "Durdurmak için Ctrl+C" "Gray"
Write-Host ""

# ── FileSystemWatcher kurulumu ───────────────────────────────
$watchers = @()

function New-Watcher {
    param([string]$path, [string]$srcRoot, [string]$dstRoot)
    $w = New-Object System.IO.FileSystemWatcher
    $w.Path                = $path
    $w.IncludeSubdirectories = $true
    $w.NotifyFilter        = [System.IO.NotifyFilters]'FileName,LastWrite,DirectoryName'
    $w.Filter              = "*.*"
    $w.EnableRaisingEvents = $true

    # Debounce: aynı dosya için 300ms içinde tekrar tetiklenmesin
    $debounce = @{}

    $action = {
        $f = $Event.SourceEventArgs.FullPath
        $ct = $Event.SourceEventArgs.ChangeType
        # Klasör, geçici, .git olaylarını yoksay
        if ((Test-Path $f -PathType Container)) { return }
        if ($f -match '\\.git\\|~$|\\.tmp$') { return }
        $now = [datetime]::Now
        if ($debounce.ContainsKey($f) -and ($now - $debounce[$f]).TotalMilliseconds -lt 300) { return }
        $debounce[$f] = $now
        Sync-File $f $Event.MessageData.Src $Event.MessageData.Dst
    }

    $data = @{ Src = $srcRoot; Dst = $dstRoot }
    Register-ObjectEvent $w "Changed" -Action $action -MessageData $data | Out-Null
    Register-ObjectEvent $w "Created" -Action $action -MessageData $data | Out-Null
    Register-ObjectEvent $w "Renamed" -Action $action -MessageData $data | Out-Null
    return $w
}

$watchers += New-Watcher $SRC $SRC $DST

if (Test-Path $PLUGINS_SRC) {
    $watchers += New-Watcher $PLUGINS_SRC $PLUGINS_SRC $PLUGINS_DST
}

# ── Ana döngü ────────────────────────────────────────────────
try {
    while ($true) { Start-Sleep -Seconds 1 }
} finally {
    foreach ($w in $watchers) {
        $w.EnableRaisingEvents = $false
        $w.Dispose()
    }
    Get-EventSubscriber | Unregister-Event -ErrorAction SilentlyContinue
    Write-Stamp "Sync durduruldu." "Gray"
}
