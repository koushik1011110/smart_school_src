param(
  [string]$path = 'c:\xampp\htdocs\smart_school_src\application'
)
Get-ChildItem -LiteralPath $path -Force | Where-Object { $_.PSIsContainer } | ForEach-Object {
  $count = (Get-ChildItem -LiteralPath $_.FullName -Recurse -Force -File -ErrorAction SilentlyContinue | Measure-Object).Count
  [PSCustomObject]@{ Path = $_.FullName; Count = $count }
} | Sort-Object -Property Count -Descending | Format-Table -AutoSize
