using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
public sealed class ConsultasLookupsController(IConsultaLookupService service) : ControllerBase
{
    [HttpGet("api/procedimentos")]
    public async Task<IActionResult> Procedimentos() =>
        Ok(ResultWrapper<List<ProcedimentoDto>>.Ok(await service.ListarProcedimentosAsync()));

    [Authorize(Roles = Roles.Gestao)]
    [HttpGet("api/usuarios")]
    public async Task<IActionResult> Usuarios() =>
        Ok(ResultWrapper<List<UsuarioDto>>.Ok(await service.ListarUsuariosAsync()));

    [Authorize(Roles = Roles.Administrador)]
    [HttpGet("api/usuarios/{id:int}")]
    public async Task<IActionResult> Usuario(int id) => await service.BuscarUsuarioAsync(id) is { } u ?
        Ok(ResultWrapper<UsuarioDto>.Ok(u)) : NotFound(ResultWrapper<object>.NotFound("Usuário não encontrado."));
}
