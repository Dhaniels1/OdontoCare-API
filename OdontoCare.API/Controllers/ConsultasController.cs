
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.API.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ConsultasController : ControllerBase
{
    private readonly IConsultaService _consultaService;

    public ConsultasController(IConsultaService consultaService)
    {
        _consultaService = consultaService;
    }

    // GET: api/Consultas
    [HttpGet]
    public async Task<IActionResult> GetTodas()
    {
        var consultas = await _consultaService.GetTodasAsync();
        return Ok(consultas);
    }

    // GET: api/Consultas/1
    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id)
    {
        var consulta = await _consultaService.GetPorIdAsync(id);

        if (consulta == null)
        {
            return NotFound(new
            {
                mensagem = "Consulta não encontrada."
            });
        }

        return Ok(consulta);
    }

    // POST: api/Consultas
    [HttpPost]
    public async Task<IActionResult> Criar(
        [FromBody] ConsultaCreateDto dados)
    {
        var consulta = new Consulta
        {
            DataConsulta = dados.DataConsulta,
            HorarioConsulta = dados.HorarioConsulta,
            StatusConsulta = dados.StatusConsulta,
            Observacoes = dados.Observacoes,
            IdUsuario = dados.IdUsuario,
            IdPaciente = dados.IdPaciente,
            IdProcedimento = dados.IdProcedimento
        };

        try
        {
            var criada = await _consultaService.CriarAsync(consulta);

            return CreatedAtAction(
                nameof(GetPorId),
                new { id = criada.IdConsulta },
                criada
            );
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new
            {
                mensagem = ex.Message
            });
        }
    }

    // PUT: api/Consultas/1
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(
        int id,
        [FromBody] ConsultaUpdateDto dados)
    {
        var consulta = new Consulta
        {
            DataConsulta = dados.DataConsulta,
            HorarioConsulta = dados.HorarioConsulta,
            StatusConsulta = dados.StatusConsulta,
            Observacoes = dados.Observacoes,
            IdUsuario = dados.IdUsuario,
            IdPaciente = dados.IdPaciente,
            IdProcedimento = dados.IdProcedimento
        };

        try
        {
            var atualizada = await _consultaService.AtualizarAsync(id, consulta);

            if (!atualizada)
            {
                return NotFound(new
                {
                    mensagem = "Consulta não encontrada."
                });
            }

            return NoContent();
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new
            {
                mensagem = ex.Message
            });
        }
    }

    // DELETE: api/Consultas/1
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id)
    {
        var excluida = await _consultaService.ExcluirAsync(id);

        if (!excluida)
        {
            return NotFound(new
            {
                mensagem = "Consulta não encontrada."
            });
        }

        return Ok(new
        {
            mensagem = "Consulta excluída com sucesso."
        });
    }
}
