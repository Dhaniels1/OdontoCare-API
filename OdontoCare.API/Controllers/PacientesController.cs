using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;
using OdontoCare.Domain.Entities;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
[Route("api/pacientes")]
public sealed class PacientesController(IPacienteService service) : ControllerBase
{
    private static PacienteResponseDto Map(Paciente p) => new()
    {
        IdPaciente = p.IdPaciente, Nome = p.Nome, Cpf = p.Cpf, Telefone = p.Telefone,
        Email = p.Email, Cep = p.Cep, Logradouro = p.Logradouro, Numero = p.Numero,
        Bairro = p.Bairro, Cidade = p.Cidade, Estado = p.Estado,
        DataNascimento = p.DataNascimento, HistoricoOdontologico = p.HistoricoOdontologico,
        Alergias = p.Alergias, Exames = p.Exames, TratamentosAnteriores = p.TratamentosAnteriores,
        ProntuarioClinicoDigital = p.ProntuarioClinicoDigital
    };

    [HttpGet]
    public async Task<IActionResult> GetTodos() =>
        Ok(ResultWrapper<List<PacienteResponseDto>>.Ok((await service.GetTodosAsync()).Select(Map).ToList()));

    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id) => await service.GetPorIdAsync(id) is { } p ?
        Ok(ResultWrapper<PacienteResponseDto>.Ok(Map(p))) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] PacienteCreateDto dto)
    {
        var cpf = dto.Cpf.Trim();
        if (await service.CpfExisteAsync(cpf))
            return Conflict(ResultWrapper<object>.Failure("CPF já cadastrado."));
        var criado = await service.CriarAsync(new Paciente
        {
            Nome = dto.Nome.Trim(), Cpf = cpf, Telefone = dto.Telefone, Email = dto.Email,
            Cep = dto.Cep, Logradouro = dto.Logradouro, Numero = dto.Numero,
            Bairro = dto.Bairro, Cidade = dto.Cidade, Estado = dto.Estado,
            DataNascimento = dto.DataNascimento, HistoricoOdontologico = dto.HistoricoOdontologico,
            Alergias = dto.Alergias, Exames = dto.Exames,
            TratamentosAnteriores = dto.TratamentosAnteriores,
            ProntuarioClinicoDigital = dto.ProntuarioClinicoDigital
        });
        return CreatedAtAction(nameof(GetPorId), new { id = criado.IdPaciente },
            ResultWrapper<PacienteResponseDto>.Ok(Map(criado), "Paciente cadastrado."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] PacienteUpdateDto dto)
    {
        var atualizado = await service.AtualizarAsync(id, new Paciente
        {
            Nome = dto.Nome.Trim(), Telefone = dto.Telefone, Email = dto.Email,
            Cep = dto.Cep, Logradouro = dto.Logradouro, Numero = dto.Numero,
            Bairro = dto.Bairro, Cidade = dto.Cidade, Estado = dto.Estado,
            DataNascimento = dto.DataNascimento, HistoricoOdontologico = dto.HistoricoOdontologico,
            Alergias = dto.Alergias, Exames = dto.Exames,
            TratamentosAnteriores = dto.TratamentosAnteriores,
            ProntuarioClinicoDigital = dto.ProntuarioClinicoDigital
        });
        if (!atualizado) return NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));
        return Ok(ResultWrapper<PacienteResponseDto>.Ok(Map((await service.GetPorIdAsync(id))!), "Paciente atualizado."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id) ?
        Ok(ResultWrapper<object>.Ok(null, "Paciente excluído.")) :
        NotFound(ResultWrapper<object>.NotFound("Paciente não encontrado."));
}
