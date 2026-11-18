using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common.Models;
using OdontoCare.Application.Common.Security;
using OdontoCare.Application.Features.Auth.DTOs;
using OdontoCare.Application.Features.Consultas.Interfaces;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
public sealed class ConsultasLookupsController(IConsultaLookupService service) : ControllerBase
{
    [Authorize(Roles = Roles.Gestao)]
    [HttpGet("api/usuarios")]
    public async Task<IActionResult> Usuarios() =>
        Ok(ResultWrapper<List<UsuarioDto>>.Ok(await service.ListarUsuariosAsync()));

    [Authorize(Roles = Roles.Administrador)]
    [HttpGet("api/usuarios/{id:int}")]
    public async Task<IActionResult> Usuario(int id) => await service.BuscarUsuarioAsync(id) is { } u ?
        Ok(ResultWrapper<UsuarioDto>.Ok(u)) : NotFound(ResultWrapper<object>.NotFound("Usuário não encontrado."));
}
