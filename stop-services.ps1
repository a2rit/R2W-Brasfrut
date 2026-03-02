# Abra o PowerShell como Administrador e cole o código abaixo

#check if is admin
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Execute como administrador"
    Write-Host -NoNewLine "Execute como administrador"
    Return
}

Write-Output "Desativando Laravel Scheduled..."
Disable-ScheduledTask -TaskName "Laravel - Produção"

while ((Get-ScheduledTask -TaskName "Laravel - Produção").State  -ne "Disabled") {
    Write-Output "Aguarde a tarefa finalizar..."
    Sleep -Seconds 3
}
Write-Output "Tarefa Finalizada"

Write-Output "Parando Serviço..."
Stop-Service -Name "LaravelProd"
while ((Get-Service -Name "Laravel - Produção").Status  -ne "Stopped") {
    Write-Output "Aguarde o serviço finalizar..."
    Sleep -Seconds 3
}
Write-Output "Serviço parado."

Write-Host -NoNewLine "Pode atualizar o sistema!"
