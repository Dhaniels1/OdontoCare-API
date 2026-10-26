using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Route("api/auth")]
public sealed class AuthController(IAuthService service) : ControllerBase
{
    [AllowAnonymous]
    [HttpPost("login")]
    public async Task<IActionResult> Login([FromBody] LoginDto dados)
    {
        var resposta = await service.LoginAsync(dados);
        if (resposta is null)
            return Unauthorized(ResultWrapper<object>.Unauthorized("Usuário ou senha inválidos."));
        return Ok(ResultWrapper<LoginResponseDto>.Ok(resposta, "Autenticado com sucesso."));
    }

    // Cadastro de contas restrito ao Administrador; não existe registro público.
    [Authorize(Roles = Roles.Administrador)]
    [HttpPost("registro")]
    public async Task<IActionResult> Registrar([FromBody] RegistroUsuarioDto dados)
    {
        var criado = await service.RegistrarAsync(dados);
        return StatusCode(201, ResultWrapper<UsuarioDto>.Ok(criado, "Usuário cadastrado."));
    }
}
