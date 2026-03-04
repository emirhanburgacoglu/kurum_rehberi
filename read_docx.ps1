param (
    [string]$DocxPath,
    [string]$OutPath
)

$tempDir = Join-Path $env:TEMP $(New-Guid).ToString()
Expand-Archive -Path $DocxPath -DestinationPath $tempDir -Force

$xmlPath = Join-Path $tempDir "word\document.xml"
$xml = [xml](Get-Content -Path $xmlPath -Encoding UTF8)
$ns = New-Object System.Xml.XmlNamespaceManager($xml.NameTable)
$ns.AddNamespace("w", "http://schemas.openxmlformats.org/wordprocessingml/2006/main")

$textNodes = $xml.SelectNodes("//w:t", $ns)
$text = @()
foreach ($node in $textNodes) {
    if ($node.InnerText -ne $null -and $node.InnerText.Trim() -ne "") {
        $text += $node.InnerText
    }
}

$text -join "`n" | Out-File -FilePath $OutPath -Encoding utf8

Remove-Item -Path $tempDir -Recurse -Force
