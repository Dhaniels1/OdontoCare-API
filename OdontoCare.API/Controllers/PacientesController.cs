using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
[Route("api/pacientes")]
public sealed class PacientesController(IPacienteService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetTodos() =>
        Ok(ResultWrapper<List<PacienteResponseDto>>.Ok(await service.GetTodosAsync()));

    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id) => await service.GetPorIdAsync(id) is { } p ?
        Ok(ResultWrapper<PacienteResponseDto>.Ok(p)) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] PacienteCreateDto dto)
    {
        var criado = await service.CriarAsync(dto);
        return CreatedAtAction(nameof(GetPorId), new { id = criado.IdPaciente },
            ResultWrapper<PacienteResponseDto>.Ok(criado, "Paciente cadastrado."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] PacienteUpdateDto dto) =>
        await service.AtualizarAsync(id, dto) is { } atualizado ?
            Ok(ResultWrapper<PacienteResponseDto>.Ok(atualizado, "Paciente atualizado.")) :
            NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id) ?
        Ok(ResultWrapper<object>.Ok(null, "Paciente excluído.")) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));
}
