
using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;
using System.Data;

namespace OdontoCare.Infrastructure.Services;

public class ConsultaService : IConsultaService
{
    private readonly OdontoCareDbContext _context;

    public ConsultaService(OdontoCareDbContext context)
    {
        _context = context;
    }

    public async Task<List<Consulta>> GetTodasAsync()
    {
        return await _context.Consultas
            .AsNoTracking()
            .ToListAsync();
    }

    public async Task<Consulta?> GetPorIdAsync(int id)
    {
        return await _context.Consultas
            .AsNoTracking()
            .FirstOrDefaultAsync(c => c.IdConsulta == id);
    }

    public async Task<Consulta> CriarAsync(Consulta consulta)
    {
        await ValidarRelacionamentosAsync(consulta);

        _context.Consultas.Add(consulta);
        await _context.SaveChangesAsync();

        return consulta;
    }

    public async Task<bool> AtualizarAsync(int id, Consulta dados)
    {
        var consulta = await _context.Consultas
            .FirstOrDefaultAsync(c => c.IdConsulta == id);

        if (consulta == null)
            return false;

        await ValidarRelacionamentosAsync(dados);

        consulta.DataConsulta = dados.DataConsulta;
        consulta.HorarioConsulta = dados.HorarioConsulta;
        consulta.StatusConsulta = dados.StatusConsulta;
        consulta.Observacoes = dados.Observacoes;
        consulta.IdUsuario = dados.IdUsuario;
        consulta.IdPaciente = dados.IdPaciente;
        consulta.IdProcedimento = dados.IdProcedimento;

        await _context.SaveChangesAsync();

        return true;
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var consulta = await _context.Consultas
            .FirstOrDefaultAsync(c => c.IdConsulta == id);

        if (consulta == null)
            return false;

        _context.Consultas.Remove(consulta);
        await _context.SaveChangesAsync();

        return true;
    }

    private async Task ValidarRelacionamentosAsync(Consulta consulta)
    {
        var pacienteExiste = await _context.Pacientes
            .AnyAsync(p => p.IdPaciente == consulta.IdPaciente);

        if (!pacienteExiste)
            throw new InvalidOperationException(
                "O paciente informado não existe.");

        var usuarioExiste = await RegistroExisteAsync(
            "usuario", "idUsuario", consulta.IdUsuario);

        if (!usuarioExiste)
            throw new InvalidOperationException(
                "O usuário informado não existe.");

        var procedimentoExiste = await RegistroExisteAsync(
            "procedimento", "idProcedimento", consulta.IdProcedimento);

        if (!procedimentoExiste)
            throw new InvalidOperationException(
                "O procedimento informado não existe.");
    }

    private async Task<bool> RegistroExisteAsync(
        string tabela,
        string coluna,
        int id)
    {
        var conexao = _context.Database.GetDbConnection();
        var abriuConexao = conexao.State != ConnectionState.Open;

        if (abriuConexao)
            await conexao.OpenAsync();

        try
        {
            await using var comando = conexao.CreateCommand();

            comando.CommandText =
                $"SELECT COUNT(*) FROM `{tabela}` WHERE `{coluna}` = @id";

            var parametro = comando.CreateParameter();
            parametro.ParameterName = "@id";
            parametro.Value = id;
            comando.Parameters.Add(parametro);

            var resultado = await comando.ExecuteScalarAsync();

            return Convert.ToInt64(resultado) > 0;
        }
        finally
        {
            if (abriuConexao)
                await conexao.CloseAsync();
        }
    }
}
