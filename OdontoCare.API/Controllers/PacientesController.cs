using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common.Models;
using OdontoCare.Application.Common.Security;
using OdontoCare.Application.Features.Pacientes.DTOs;
using OdontoCare.Application.Features.Pacientes.Interfaces;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
[Route("api/pacientes")]
public sealed class PacientesController(IPacienteService service) : ControllerBase
{
    // ID do dentista vindo exclusivamente do JWT validado, nunca da query string.
    private int? DentistaId => User.IsInRole(Roles.Dentista)
        ? int.TryParse(User.FindFirst("sub")?.Value, out var id) ? id : 0
        : null;

    [HttpGet]
    public async Task<IActionResult> GetTodos() =>
        Ok(ResultWrapper<List<PacienteResponseDto>>.Ok(await service.GetTodosAsync(DentistaId)));

    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id) => await service.GetPorIdAsync(id, DentistaId) is { } p ?
        Ok(ResultWrapper<PacienteResponseDto>.Ok(p)) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));

    // Campos clínicos nunca integram os retornos comuns. Secretária recebe 403.
    // Dentista só acessa prontuários de pacientes vinculados às suas consultas.
    [Authorize(Roles = Roles.Clinico)]
    [HttpGet("{id:int}/prontuario")]
    [ResponseCache(NoStore = true, Location = ResponseCacheLocation.None)]
    public async Task<IActionResult> GetProntuario(int id) => await service.GetProntuarioAsync(id, DentistaId) is { } prontuario ?
        Ok(ResultWrapper<PacienteProntuarioDto>.Ok(prontuario)) :
        NotFound(ResultWrapper<object>.NotFound("Prontuário não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] PacienteCreateDto dto)
    {
        var criado = await service.CriarAsync(dto, User.IsInRole(Roles.Administrador));
        return CreatedAtAction(nameof(GetPorId), new { id = criado.IdPaciente },
            ResultWrapper<PacienteResponseDto>.Ok(criado, "Paciente cadastrado."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] PacienteUpdateDto dto) =>
        await service.AtualizarAsync(id, dto, User.IsInRole(Roles.Administrador)) is { } atualizado ?
            Ok(ResultWrapper<PacienteResponseDto>.Ok(atualizado, "Paciente atualizado.")) :
            NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id) ?
        Ok(ResultWrapper<object>.Ok(null, "Paciente excluído.")) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));
}
