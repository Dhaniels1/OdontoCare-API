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
[Route("api/consultas")]
public sealed class ConsultasController(IConsultaService service, IConsultaLookupService lookups) : ControllerBase
{
    private bool SoProprias => User.IsInRole(Roles.Dentista);
    private int UsuarioId => int.TryParse(User.FindFirst("sub")?.Value, out var id) ? id : 0;
    private static Consulta Map(ConsultaCreateDto d) => new()
    {
        DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
        StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
        IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
    };
    private static Consulta Map(ConsultaUpdateDto d) => new()
    {
        DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
        StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
        IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
    };

    [HttpGet]
    public async Task<IActionResult> GetTodas()
    {
        var todas = await lookups.ListarAsync(SoProprias ? UsuarioId : null);
        return Ok(ResultWrapper<List<ConsultaListaDto>>.Ok(todas));
    }

    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id)
    {
        var c = await service.GetPorIdAsync(id);
        if (c is null || (SoProprias && c.IdUsuario != UsuarioId))
            return NotFound(ResultWrapper<object>.NotFound("Consulta não encontrada."));
        return Ok(ResultWrapper<Consulta>.Ok(c));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] ConsultaCreateDto dados)
    {
        var criada = await service.CriarAsync(Map(dados));
        return CreatedAtAction(nameof(GetPorId), new { id = criada.IdConsulta },
            ResultWrapper<Consulta>.Ok(criada, "Consulta cadastrada."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] ConsultaUpdateDto dados)
    {
        var ok = await service.AtualizarAsync(id, Map(dados));
        return ok ? Ok(ResultWrapper<object>.Ok(null, "Consulta atualizada.")) :
            NotFound(ResultWrapper<object>.NotFound("Consulta não encontrada."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPatch("{id:int}/cancelar")]
    public async Task<IActionResult> Cancelar(int id)
    {
        var ok = await service.CancelarAsync(id);
        return ok ? Ok(ResultWrapper<object>.Ok(null, "Consulta cancelada e preservada no histórico.")) :
            NotFound(ResultWrapper<object>.NotFound("Consulta não encontrada."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id)
    {
        var ok = await service.ExcluirAsync(id);
        return ok ? Ok(ResultWrapper<object>.Ok(null, "Consulta cancelada e preservada no histórico.")) :
            NotFound(ResultWrapper<object>.NotFound("Consulta não encontrada."));
    }
}
