# Abra o PowerShell como Administrador e cole o código abaixo

$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Execute como administrador"
    Write-Host -NoNewLine "Execute como administrador"
    Return
}

Write-Output "Habilitando Laravel Scheduled..."
Enable-ScheduledTask -TaskName "Laravel - Produção"

Write-Output "Iniciando Serviço..."
Start-Service -Name "LaravelProd"
Write-Output "Serviço iniciado."

Write-Host -NoNewLine "Sistema em execução!"
