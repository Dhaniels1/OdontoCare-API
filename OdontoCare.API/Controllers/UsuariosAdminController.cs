using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Administrador)]
[Route("api/usuarios")]
public sealed class UsuariosAdminController(IUsuarioAdminService service) : ControllerBase
{
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] UsuarioUpdateDto dto) =>
        await service.AtualizarAsync(id, dto) is { } u ?
            Ok(ResultWrapper<UsuarioDto>.Ok(u, "Usuário atualizado.")) :
            NotFound(ResultWrapper<object>.NotFound("Usuário não encontrado."));

    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id)
    {
        var executor = int.TryParse(User.FindFirst("sub")?.Value, out var userId) ? userId : 0;
        return await service.ExcluirAsync(id, executor) ?
            Ok(ResultWrapper<object>.Ok(null, "Usuário excluído.")) :
            NotFound(ResultWrapper<object>.NotFound("Usuário não encontrado."));
    }
}
